<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Product;
use App\Models\User;
use App\Models\Expense;
use App\Models\ProductReview;
use Auth;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        if (!auth()->user()->can('dashboard.access')) {
            abort(403, 'unauthorized');
        }

        $status = $request->status;
        $q      = $request->q;

        $query = Order::whereHas('details.product', function ($q) {
            $q->whereNotNull('name');
        });

        if (!empty($q)) {
            $query->where(function ($row) use ($q) {
                $row->where('invoice_no', 'Like', '%'.$q.'%');
            });
        }

        if (!empty($status)) {
            $query->where('status', 'Like', '%'.$status.'%');
        }

        // Worker হলে শুধু নিজের assigned অর্ডার
        if (Auth::user()->hasRole('worker')) {
            $query->where('assign_user_id', Auth::id());
        }

        $items        = $query->latest()->take(20)->get();
        $statuses     = getOrderStatus();
        $total_stocks = Product::select('stock_quantity')->sum('stock_quantity');
        $isWorker     = Auth::user()->hasRole('worker'); // worker কি না চেক

        return view('backend.dashboard', compact('items', 'status', 'q', 'statuses', 'total_stocks', 'isWorker'));
    }

    /**
     * KPI JSON (টপ কার্ডস)
     */
    public function getDashboardData2(Request $request)
    {
        $user      = auth()->user();
        $userStart = optional($user->created_at)?->startOfDay() ?? now()->startOfDay();

        $startDateUi = $request->filled('startDate') ? $request->startDate : $userStart->toDateString();
        $endDateUi   = $request->filled('endDate')   ? $request->endDate   : now()->toDateString();

        $start = Carbon::parse($startDateUi)->startOfDay();
        $end   = Carbon::parse($endDateUi)->addDay()->startOfDay();

        if ($start->lt($userStart)) $start = $userStart->copy();
        if ($end->lte($start))      $end   = $start->copy()->addDay();

        $base = Order::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<',  $end)
            ->when($user->hasRole('worker') && !$user->can('order.view_all'), function ($q) use ($user) {
                $q->where('assign_user_id', $user->id);
            });

        $total_orders    = (clone $base)->count();
        $pending_orders  = (clone $base)->where('status', 'pending')->count();
        $complete_orders = (clone $base)->whereIn('status', ['complete','completed','delivered'])->count();
        $cancell_orders  = (clone $base)->whereIn('status', ['cancell','cancel','canceled','cancelled'])->count();
        $sell_amount     = (clone $base)->sum('final_amount');

        $profitQuery = Order::query()
            ->whereBetween('orders.created_at', [$start, $end])
            ->whereIn('orders.status', ['complete','completed','delivered'])
            ->when($user->hasRole('worker') && !$user->can('order.view_all'), function ($q) use ($user) {
                $q->where('orders.assign_user_id', $user->id);
            })
            ->join('order_details', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->select(DB::raw('
                SUM(
                    (order_details.unit_price -
                        IF(order_details.purchase_price IS NOT NULL AND order_details.purchase_price > 0,
                           order_details.purchase_price,
                           products.purchase_prices
                        )
                    ) * order_details.quantity
                ) as profit
            '));

        $grossProfit   = $profitQuery->value('profit') ?: 0;

        $totalExpense  = Expense::whereDate('date', '>=', $start->toDateString())
                            ->whereDate('date', '<',  $end->toDateString())
                            ->sum('amount');

        $netProfit     = $grossProfit - $totalExpense;

        return response()->json([
            'success'         => true,
            'profit'          => $netProfit,
            'totalExpense'    => $totalExpense,
            'total_orders'    => $total_orders,
            'pending_orders'  => $pending_orders,
            'complete_orders' => $complete_orders,
            'cancell_orders'  => $cancell_orders,
            'sell_amount'     => $sell_amount,
        ]);
    }

    public function index()
    {
        $s = request('q');
        $query = ProductReview::latest();

        if (!empty($s)) {
            $query->where(function ($row) use ($s) {
                $row->where('name', 'Like', '%'.$s.'%');
            });
        }

        $data = $query->paginate(30);
        return view('backend.review.index', compact('data'));
    }

    public function destroy($id)
    {
        ProductReview::destroy($id);
        return response()->json(['status' => true, 'msg' => 'User has been deleted']);
    }

    /**
     * নিচের পার্শিয়াল (কার্ডের নিচে থাকা সেকশন)
     */
    public function getDashboardData(Request $request)
    {
        $workerCount = User::whereHas('roles', function ($query) {
            $query->where('name', 'worker');
        })->count();

        $data['products']           = Product::count();
        $data['orders']             = Order::count();
        $data['users']              = $workerCount;
        $data['current_month_sell'] = Order::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                                        ->sum('final_amount');
        $data['today_sell']         = Order::whereDate('created_at', now()->toDateString())->sum('final_amount');
        $data['prev_month_sell']    = Order::whereBetween(
                                        'created_at',
                                        [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()]
                                      )->sum('final_amount');

        return view('backend.partials.dashboard_data', $data);
    }

    public function reviewAction(Request $request)
    {
        $ids = $request->ids ?? [];

        if (empty($ids)) {
            return response()->json(['status' => false, 'msg' => 'No reviews selected!']);
        }

        if ($request->has('delete')) {
            ProductReview::whereIn('id', $ids)->delete();
            return response()->json(['status' => true, 'msg' => 'Selected reviews deleted successfully!']);
        }

        if ($request->has('status')) {
            $status = $request->status == 1 ? 1 : 0;
            ProductReview::whereIn('id', $ids)->update(['status' => $status]);
            $msg = $status ? 'Selected reviews approved!' : 'Selected reviews rejected!';
            return response()->json(['status' => true, 'msg' => $msg]);
        }

        return response()->json(['status' => false, 'msg' => 'Invalid action!']);
    }
}
