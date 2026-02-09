<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Information;
use App\Models\OrderDetails;
use App\Models\DeliveryCharge;
use App\Models\Variation;
use App\Models\Courier;
use App\Models\User;
use App\Models\BlockedIp;
use App\Models\Category;
use App\Models\Product;
use Auth;
use App\Utils\Util;
use App\Exports\OrderExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    private $redx_api_base_url = '';
    private $redx_api_access_token = '';
    private $pathao_api_base_url = '';
    private $pathao_api_access_token = '';
    private $pathao_store_id = '';
    private $steadfast_api_base_url = '';
    private $steadfast_api_key = '';
    private $steadfast_secret_key = '';
    private $util = '';

    public function __construct(Util $util)
    {
        $info = Information::first();

        $this->redx_api_base_url = rtrim($info->redx_api_base_url ?? '', '/') . '/';
        $this->redx_api_access_token = 'Bearer ' . ($info->redx_api_access_token ?? '');

        $this->pathao_api_base_url = rtrim($info->pathao_api_base_url ?? '', '/') . '/';
        $this->pathao_api_access_token = $info->pathao_api_access_token ?? '';
        $this->pathao_store_id = $info->pathao_store_id ?? '';

        $this->steadfast_api_base_url = rtrim($info->steadfast_api_base_url ?? '', '/');
        $this->steadfast_api_key = $info->steadfast_api_key ?? '';
        $this->steadfast_secret_key = $info->steadfast_secret_key ?? '';

        $this->util = $util;
    }

    /** ===============================
     *  Worker helpers (tolerant + logging)
     *  =============================== */
    private function getActiveWorkerIds(): \Illuminate\Support\Collection
    {
        $workers = User::query()
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereIn('status', [1, '1', true, 'true', 'active', 'Active']);
            })
            ->when(
                Schema::hasColumn((new User)->getTable(), 'deleted_at'),
                fn($q) => $q->whereNull('deleted_at')
            )
            ->whereHas('roles', fn($q) => $q->whereRaw('LOWER(name)=?', ['worker']))
            ->orderBy('id')
            ->pluck('id')
            ->values();

        Log::info('Assignable workers (DB list)', ['ids' => $workers->toArray()]);
        return $workers;
    }

    /**
     * === DB-Driven Fair Distribution ===
     * আজকের দিনে (system timezone) যাঁর অর্ডার কম, তাঁকেই দিন।
     * টাই হলে ছোট আইডি আগে। কোনও ক্যাশ/পয়েন্টার নয়।
     */
    private function pickNextWorkerId(): int
    {
        $activeIds = $this->getActiveWorkerIds();
        if ($activeIds->isEmpty()) {
            throw new \Exception('No active workers found to assign.');
        }

        $candidateId = DB::table('users as u')
            ->join('model_has_roles as m', 'm.model_id', '=', 'u.id')
            ->join('roles as r', 'r.id', '=', 'm.role_id')
            ->leftJoin('orders as o', function ($join) {
                $join->on('o.assign_user_id', '=', 'u.id')
                    ->whereDate('o.created_at', DB::raw('CURDATE()'));
            })
            ->whereIn('u.id', $activeIds->toArray())
            ->whereRaw('LOWER(r.name) = ?', ['worker'])
            ->where(function ($q) {
                $q->whereNull('u.status')
                    ->orWhereIn('u.status', [1, '1', true, 'true', 'active', 'Active']);
            })
            ->when(
                Schema::hasColumn((new User)->getTable(), 'deleted_at'),
                fn($q) => $q->whereNull('u.deleted_at')
            )
            ->groupBy('u.id')
            ->orderByRaw('COUNT(o.id) ASC')
            ->orderBy('u.id', 'ASC')
            ->value('u.id');

        if (!$candidateId) {
            $candidateId = $activeIds->first();
        }

        Log::info('DB fair pick -> chosen worker', ['id' => (int)$candidateId]);
        return (int)$candidateId;
    }

    private function assertIsWorker(int $userId): void
    {
        $ok = User::where('id', $userId)
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereIn('status', [1, '1', true, 'true', 'active', 'Active']);
            })
            ->when(
                Schema::hasColumn((new User)->getTable(), 'deleted_at'),
                fn($q) => $q->whereNull('deleted_at')
            )
            ->whereHas('roles', fn($q) => $q->whereRaw('LOWER(name)=?', ['worker']))
            ->exists();

        if (!$ok) {
            Log::warning('assertIsWorker failed', ['user_id' => $userId]);
            throw new \Exception('Assigned user must be an active worker.');
        }
    }

    /** Unique invoice */
    private function generateUniqueInvoice(): string
    {
        do {
            $candidate = (string) random_int(111111, 999999);
        } while (Order::where('invoice_no', $candidate)->exists());
        return $candidate;
    }

    public function orderExport()
    {
        return Excel::download(new OrderExport, 'orders.xlsx');
    }

    /** ===========================
     *  Index
     *  =========================== */
    public function index(Request $request)
    {
        if (!auth()->user()->can('order.view')) abort(403, 'unauthorized');

        $status = $request->status;
        $q = $request->q;

        $query = Order::with(['details.product', 'assign', 'courier'])->latest();

        if (!empty($q)) {
            $query->where(function ($row) use ($q) {
                $row->where('invoice_no', 'like', '%' . $q . '%')
                    ->orWhere('first_name', 'like', '%' . $q . '%')
                    ->orWhere('last_name', 'like', '%' . $q . '%')
                    ->orWhere('mobile', 'like', '%' . $q . '%')
                    ->orWhere('shipping_address', 'like', '%' . $q . '%');
            });
        }

        if (!empty($status)) $query->where('status', 'like', '%' . $status . '%');

        if (Auth::user()->hasRole('worker')) $query->where('assign_user_id', Auth::id());

        $yes_count = Order::whereNotNull('courier_tracking_id')->where('status', 'courier')->count();
        $no_count  = Order::whereNull('courier_tracking_id')->where('status', 'courier')->count();

        $items = $query->paginate(30)->appends($request->all());

        if ($request->ajax()) return view('backend.orders.received_order', compact('items'))->render();

        return view('backend.orders.index', compact('items', 'status', 'q', 'yes_count', 'no_count'));
    }

    public function IPBlock()
    {
        return redirect('backend.reports.ipblock.ipblock');
    }

    public function IPBlockSubmit(Request $request)
    {
        $request->validate(['ip_address' => 'required|ip', 'reason' => 'required|string']);
        $ip = $request->input('ip_address');
        $reason = $request->input('reason');
        if (BlockedIp::where('ip_address', $ip)->exists()) return back()->with('error', 'IP address is already blocked.');
        BlockedIp::create(['ip_address' => $ip, 'reason' => $reason]);
        return back()->with('success', 'IP address blocked successfully.');
    }

    public function create()
    {
        $status  = getOrderStatus();
        $charges = DeliveryCharge::all();
        $couriers = Courier::all();
        $areas   = $this->getRedxAreaList();
        $cities  = $this->getPathaoCityList();
        return view('backend.orders.create', compact('status', 'charges', 'couriers', 'areas', 'cities'));
    }

    /** ===========================
     *  STORE (DB-fair auto-assign)
     *  =========================== */
    public function store(Request $request)
    {
        if (!auth()->user()->can('order.create')) abort(403, 'unauthorized');

        $data = $request->validate([
            'note'               => '',
            'first_name'         => 'required',
            'last_name'          => '',
            'mobile'             => 'required',
            'zip_code'           => '',
            'area_id'            => '',
            'area_name'          => '',
            'city'               => '',
            'state'              => '',
            'store_id'           => '',
            'weight'             => '',
            'shipping_address'   => 'min:10',
            'courier_id'         => '',
            'date'               => 'required',
            'status'             => 'required',
            'discount'           => '',
            'shipping_charge'    => 'required|numeric',
            'delivery_charge_id' => 'required',
            'final_amount'       => 'required|numeric',
        ]);

        $data['amount']     = ($data['final_amount'] ?? 0) + ($data['shipping_charge'] ?? 0) + ($data['discount'] ?? 0);
        $data['user_id']    = auth()->id();
        $data['invoice_no'] = $this->generateUniqueInvoice();

        unset($data['assign_user_id']);

        if (auth()->user()->hasRole('worker')) {
            $data['assign_user_id'] = (int) auth()->id();
        } else {
            $data['assign_user_id'] = (int) $this->pickNextWorkerId();
        }

        DB::beginTransaction();
        try {
            if (empty($data['assign_user_id'])) throw new \Exception('No worker available to assign.');

            $order = Order::create($data);

            if ((int)($order->assign_user_id ?? 0) !== (int)$data['assign_user_id']) {
                $order->assign_user_id = (int)$data['assign_user_id'];
                $order->save();
            }

            $lines = [];
            if (isset($request->product_id) && is_array($request->product_id)) {
                foreach ($request->product_id as $k => $pid) {
                    $qty = (int)($request->quantity[$k] ?? 1);
                    $vid = (int)($request->variation_id[$k] ?? 0);
                    $lines[] = [
                        'product_id'   => (int)$pid,
                        'quantity'     => $qty,
                        'variation_id' => $vid,
                        'unit_price'   => (float)($request->unit_price[$k] ?? 0),
                        'discount'     => (float)($request->unit_discount[$k] ?? 0),
                    ];
                    $this->util->decreaseProductStock((int)$pid, (int)$vid, $qty);
                }
            } elseif (isset($request->variation_id) && is_array($request->variation_id)) {
                foreach ($request->variation_id as $k => $vid) {
                    $variation = Variation::with('product')->find((int)$vid);
                    if (!$variation) continue;
                    $pid = (int)$variation->product_id;
                    $qty = (int)($request->quantity[$k] ?? 1);
                    $lines[] = [
                        'product_id'   => $pid,
                        'quantity'     => $qty,
                        'variation_id' => (int)$vid,
                        'unit_price'   => (float)($request->unit_price[$k] ?? 0),
                        'discount'     => (float)($request->unit_discount[$k] ?? 0),
                    ];
                    $this->util->decreaseProductStock($pid, (int)$vid, $qty);
                }
            }

            if (empty($lines)) {
                Log::warning('Order create failed: no lines', ['req' => $request->all()]);
                throw new \Exception('No order lines provided.');
            }

            $order->details()->createMany($lines);

            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Order Created & Assigned!', 'url' => route('admin.orders.index')]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order create failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        if (!auth()->user()->can('order.view')) abort(403, 'unauthorized');
        $item = Order::with('details', 'details.product', 'payments', 'delivery_charge')->find($id);
        if ($item && $item->status == 'processing') {
            $item->status = 'courier';
            $item->save();
        }
        $info = Information::first();
        return view('backend.orders.show_prnt', compact('item', 'info'));
    }

    public function edit($id)
    {
        $item = Order::with('details', 'details.product', 'payments', 'assign')->find($id);
        $orderbyNumber = Order::with('details', 'details.product', 'assign')->where('mobile', $item->mobile ?? '')->get();

        $status  = getOrderStatus();
        $charges = DeliveryCharge::all();
        $couriers = Courier::all();
        $areas   = $this->getRedxAreaList();
        $cities  = $this->getPathaoCityList();
        return view('backend.orders.edit', compact('item', 'status', 'charges', 'couriers', 'areas', 'cities', 'orderbyNumber'));
    }

    public function orderList()
    {
        if (!auth()->user()->can('order.view')) abort(403, 'unauthorized');

        $items = Order::with('details', 'details.product', 'payments')->whereIn('id', request('order_ids'))->get();

        $status_array = [];
        foreach ($items as $item) $status_array[] = $item->status;

        if (in_array('pending', $status_array)) {
            return response()->json(['status' => false, 'msg' => 'Pending Order Found !!']);
        } else {
            foreach ($items as $item) {
                if ($item->status == 'processing') {
                    $item->status = 'courier';
                    $item->save();
                }
            }
        }

        $info = Information::first();
        $view = view('backend.orders.show', compact('items', 'info'))->render();
        return response()->json(['status' => true, 'items' => $items, 'info' => $info, 'view' => $view]);
    }

    public function getOrderProduct(Request $request)
    {
        $data = Variation::join('products', 'products.id', 'variations.product_id')
            ->join('sizes', 'sizes.id', 'variations.size_id')
            ->join('colors', 'colors.id', 'variations.color_id')
            ->select("variations.id", DB::raw("TRIM(CONCAT(products.name,' (',sizes.title,'),(',colors.name,')')) AS value"))
            ->where('products.name', 'LIKE', '%' . $request->get('search') . '%')
            ->get();
        return response()->json($data);
    }

    public function getOrderProduct2(Request $request)
    {
        return $this->getOrderProduct($request);
    }

    public function orderProductEntry(Request $request)
    {
        $id = $request->id;
        $variation = Variation::with(['product', 'size', 'color', 'stocks'])->find($id);
        if (!$variation) return response()->json(['success' => false, 'msg' => 'Product Not Found !!']);
        $data = getProductInfo($variation->product);

        $html = '<tr><td><img src="/products/' . $variation->product->image . '" height="50" width="50"/></td>
                <td>' . $variation->product->name . '</td>
                <td>' . $variation->size->title . '</td>
                <td>' . $variation->color->name . '</td>
                <td>
                    <input class="form-control quantity" name="quantity[]" type="number" value="1" required min="1" data-qty="' . $variation->stocks->sum('quantity') . '"/>
                    <input type="hidden" name="variation_id[]" value="' . $variation->id . '"/>
                    <input type="hidden" name="product_id[]" value="' . $variation->product_id . '"/>
                </td>
                <td><input class="form-control unit_price" name="unit_price[]" type="number" value="' . $data['price'] . '" required/></td>
                <td><input class="form-control unit_discount" name="unit_discount[]" type="number" value="' . $data['discount_amount'] . '" required/></td>
                <td class="row_total">' . $data['price'] . '</td>
                <td><a class="remove btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></a></td>
                </tr>';
        return response()->json(['success' => true, 'html' => $html]);
    }

    public function landingProductEntry(Request $request)
    {
        $id = $request->id;
        $variation = Variation::with(['product', 'size', 'color', 'stocks'])->find($id);
        if (!$variation) return response()->json(['success' => false, 'msg' => 'Product Not Found !!']);

        $pr_id = $variation->product->id;
        $data  = getProductInfo($variation->product);

        $html = '
        <table class="table table-centered table-nowrap mb-0" id="product_table">
            <thead class="table-light">
                <tr>
                    <th>Image</th><th>Product</th><th>Size</th><th>Color</th>
                    <th style="width:120px;">Quantity</th>
                    <th style="width:150px;">Sell Price</th>
                    <th style="width:150px;">Discount</th>
                    <th>Subtotal</th><th>Action</th>
                </tr>
            </thead>
            <tbody id="data">
               <tr>
                 <td><img src="/products/' . $variation->product->image . '" height="50" width="50"/></td>
                 <td>' . $variation->product->name . '</td>
                 <td>' . $variation->size->title . '</td>
                 <td>' . $variation->color->name . '</td>
                 <td>
                    <input class="form-control quantity" name="quantity[]" type="number" value="1" required min="1" data-qty="' . $variation->stocks->sum('quantity') . '"/>
                    <input type="hidden" name="variation_id[]" value="' . $variation->id . '"/>
                    <input type="hidden" name="product_id[]"   value="' . $variation->product_id . '"/>
                 </td>
                 <td><input class="form-control unit_price" name="unit_price[]" type="number" value="' . $data['price'] . '" required/></td>
                 <td><input class="form-control unit_discount" name="unit_discount[]" type="number" value="' . $data['discount_amount'] . '" required/></td>
                 <td class="row_total">' . $data['price'] . '</td>
                 <td><a class="remove btn btn-sm btn-danger"><i class="mdi mdi-delete"></i></a></td>
               </tr>
            </tbody>
        </table>';

        return response()->json(['success' => true, 'html' => $html, 'pr_id' => $pr_id]);
    }

    /** status-wise list (AJAX) */
    public function status_wise_order(Request $request)
    {
        $statusValue  = $request->statusValue;
        $redx_status  = $request->redx_status;
        $courier_type = $request->courier_type;

        $query = Order::with(['details.product', 'courier', 'assign'])->latest();

        if (!empty($redx_status)) {
            if ($redx_status == 'yes') $query->whereNotNull('courier_tracking_id');
            else if ($redx_status == 'no') $query->whereNull('courier_tracking_id');
        }

        if (!empty($courier_type)) {
            if ($courier_type == 'none')      $query->whereNull('courier_id');
            elseif ($courier_type == 'redx')      $query->where('courier_id', 1);
            elseif ($courier_type == 'pathao')    $query->where('courier_id', 2);
            elseif ($courier_type == 'steadfast') $query->where('courier_id', 3);
        }

        if (Auth::user()->hasRole('worker')) $query->where('assign_user_id', Auth::id());

        $received_order = $query->where('status', $statusValue)
            ->paginate(30)
            ->appends([
                'statusValue'  => $statusValue,
                'redx_status'  => $redx_status,
                'courier_type' => $courier_type
            ]);

        $view = view('backend.orders.received_order', [
            'received_order' => $received_order,
            'statusValue'    => $statusValue,
            'redx_status'    => $redx_status,
            'courier_type'   => $courier_type,
            'source'         => 'status',
        ])->render();

        return response()->json(['success' => true, 'view' => $view]);
    }

    /** search (AJAX) */
    public function searchOrder(Request $request)
    {
        $searchStr = trim($request->searchValue ?? '');
        $query = Order::with(['details.product', 'assign', 'courier'])->latest();

        if (!empty($searchStr)) {
            $query->where(function ($row) use ($searchStr) {
                $row->where('invoice_no', 'like', '%' . $searchStr . '%')
                    ->orWhere('first_name', 'like', '%' . $searchStr . '%')
                    ->orWhere('last_name', 'like', '%' . $searchStr . '%')
                    ->orWhere('mobile', 'like', '%' . $searchStr . '%')
                    ->orWhere('shipping_address', 'like', '%' . $searchStr . '%');
            });
        }

        if (Auth::user()->hasRole('worker')) $query->where('assign_user_id', Auth::id());

        $received_order = $query->paginate(30)->appends(['searchValue' => $searchStr]);

        $view = view('backend.orders.received_order', [
            'received_order' => $received_order,
            'searchStr'      => $searchStr,
            'source'         => 'search',
        ])->render();

        return response()->json(['success' => true, 'view' => $view]);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('order.edit')) abort('403', 'Unauthorized');

        $order = Order::find($id);
        $data = $request->validate([
            'note' => '',
            'first_name' => '',
            'last_name' => '',
            'zip_code' => '',
            'area_name' => '',
            'city' => '',
            'state' => '',
            'store_id' => '',
            'weight' => '',
            'mobile' => '',
            'shipping_address' => 'min:10',
            'courier_id' => '',
            'courier_tracking_id' => '',
            'date' => 'required',
            'status' => 'required',
            'discount' => '',
            'shipping_charge' => '',
            'delivery_charge_id' => '',
            'final_amount' => 'required|numeric',
        ]);

        if ($request->redx_area_id != null)        $data['area_id'] = $request->redx_area_id;
        else if ($request->pathao_area_id != null) $data['area_id'] = $request->pathao_area_id;
        else                                       $data['area_id'] = null;

        if (isset($request->courier_id) && $order->status === 'pending') $data['status'] = 'processing';

        $data['amount'] = $data['final_amount'] + ($data['shipping_charge'] ?? 0) + ($data['discount'] ?? 0);

        DB::beginTransaction();

        try {
            $order->update($data);

            if (isset($request->order_line_id)) {
                $delete_line = OrderDetails::where('order_id', $id)
                    ->whereNotIn('id', $request->order_line_id)->get();

                foreach ($delete_line as $line) {
                    $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                    $line->delete();
                }
            } else {
                foreach ($order->details as $line) {
                    $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                    $line->delete();
                }
            }

            if (isset($request->product_id)) {
                $dataLines = [];
                foreach ($request->product_id as $key => $product_id) {
                    if (isset($request->order_line_id[$key])) {
                        $qty = (int)$request->quantity[$key];
                        $line_id = $request->order_line_id[$key];
                        $line = OrderDetails::find($line_id);
                        $this->util->updateProductStock($line->product_id, $line->variation_id, $qty, $line->quantity);
                        $line->quantity = $qty;
                        $line->unit_price = (float)$request->unit_price[$key];
                        $line->save();
                    } else {
                        $qty = (int)$request->quantity[$key];
                        $variation_id = (int)$request->variation_id[$key];
                        $dataLines[] = [
                            'product_id'   => (int)$product_id,
                            'quantity'     => $qty,
                            'variation_id' => $variation_id,
                            'unit_price'   => (float)$request->unit_price[$key],
                            'discount'     => (float)($request->unit_discount[$key] ?? 0),
                        ];
                        $this->util->decreaseProductStock((int)$product_id, $variation_id, $qty);
                    }
                }
                if (!empty($dataLines)) $order->details()->createMany($dataLines);
            }

            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Order Updated Successfully!', 'url' => route('admin.orders.index')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            if (!auth()->user()->can('order.delete')) abort(403, 'unauthorized');

            $item = Order::find($id);

            if ($item->details()->count()) {
                foreach ($item->details as $line) {
                    $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                }
                $item->details()->delete();
            }

            if ($item->payments()->count()) $item->payments()->delete();

            $item->delete();

            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Order Is Deleted!!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function trashed_orders(Request $request)
    {
        $status = $request->status;
        $q = $request->q;
        $query = Order::onlyTrashed()->with('details', 'details.product', 'payments');

        if (!empty($q)) {
            $query->where(function ($row) use ($q) {
                $row->where('invoice_no', 'Like', '%' . $q . '%');
            });
        }

        if (!empty($status)) {
            $query->where('status', 'Like', '%' . $status . '%');
        }

        if (Auth::user()->hasRole('worker')) {
            $query->where('assign_user_id', Auth::id());
        }

        $yes_count = Order::whereNotNull('courier_tracking_id')->where('status', 'courier')->count();
        $no_count  = Order::whereNull('courier_tracking_id')->where('status', 'courier')->count();
        $trashed_orders = $query->latest()->paginate(30);

        return view('backend.orders.trashed_orders', compact('trashed_orders', 'status', 'q', 'yes_count', 'no_count'));
    }

    public function restore_order(Request $request)
    {
        $restore_order = Order::where('id', $request->id)->withTrashed()->first();
        $restore_order_details = OrderDetails::where('order_id', $restore_order->id)->withTrashed()->get();

        foreach ($restore_order_details as $restore_details) {
            $this->util->increaseProductStock($restore_details->product_id, $restore_details->variation_id, $restore_details->quantity);
            $restore_details->restore();
        }

        $restore_order->restore();
        return response()->json(['success' => true, 'msg' => 'Order Is Restored !!']);
    }

    public function forceDel($id)
    {
        try {
            DB::beginTransaction();

            $del_orders = Order::where('id', $id)->withTrashed()->first();
            $del_order_details = OrderDetails::where('order_id', $id)->withTrashed()->get();

            foreach ($del_order_details as $del_details) {
                $this->util->decreaseProductStock($del_details->product_id, $del_details->variation_id, $del_details->quantity);
                $del_details->forceDelete();
            }

            $del_orders->forceDelete();

            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Order Is Deleted Permanently!!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function category_wise_order(Request $request)
    {
        $subCats = Category::where('parent_id', $request->category_id)->get();
        $products_data = Product::where('category_id', $request->category_id)->get();

        $products_id = [];
        foreach ($products_data as $data_p) $products_id[] = $data_p->id;

        $all_order_id = [];
        foreach ($products_id as $pr_id) {
            $details = OrderDetails::where('product_id', $pr_id)->first();
            if (isset($details->order_id)) $all_order_id[] = $details->order_id;
        }

        $items = [];
        foreach ($all_order_id as $order_id) {
            $items_order = Order::with('details', 'details.product', 'payments')->where('id', $order_id)->first();
            if ($items_order) $items[] = $items_order;
        }

        $category_id = $request->category_id;
        $sub_category_id = $request->sub_category_id;
        $all_category = Category::whereNull('parent_id')->get();
        $yes_count = Order::whereNotNull('courier_tracking_id')->where('status', 'courier')->count();
        $no_count  = Order::whereNull('courier_tracking_id')->where('status', 'courier')->count();

        return view('backend.orders.cat_wise_order', compact('items', 'yes_count', 'no_count', 'all_category', 'subCats', 'category_id', 'sub_category_id'));
    }

    public function orderStatus($id)
    {
        $item = Order::find($id);
        $status = getOrderStatus();
        return view('backend.orders.status_update', compact('item', 'status'));
    }

    public function orderStatusUPdate($id)
    {
        $item = Order::with('user', 'details')->find($id);
        foreach ($item->details as $line) {
            $old_status = $item->status;
            $change_status = request('status');
            if (($old_status == 'cancell' || $old_status == 'return') && in_array($change_status, ['pending', 'processing', 'courier', 'on_hold', 'complete'])) {
                $this->util->decreaseProductStock($line->product_id, $line->variation_id, $line->quantity);
            }
            if (in_array($old_status, ['pending', 'processing', 'courier', 'on_hold', 'complete']) && in_array($change_status, ['cancell', 'return'])) {
                $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
            }
        }

        $item->status = request('status');
        $item->save();

        return response()->json(['status' => true, 'msg' => 'Order Status Updated!']);
    }

    public function assignUser()
    {
        $users = User::whereHas('roles', function ($q) {
            $q->whereNotNull('name');
        })->get();
        return view('backend.orders.assign_user', compact('users'));
    }

    public function orderStatusUpdateMulti()
    {
        $status = getOrderStatus();
        return view('backend.orders.all_status_update', compact('status'));
    }

    public function multuOrderStatusUpdate()
    {
        foreach (request('order_ids') as $id) {
            $item = Order::with('user')->find($id);
            $item->status = request('status');
            $item->save();
        }

        return response()->json(['status' => true, 'msg' => 'Order Status Updated!!']);
    }

    public function updateCourierStatus()
    {
        foreach (request('order_ids') as $id) {
            $item = Order::with('user')->find($id);
            if ($item->courier_id == NULL || $item->courier_id !== 3) {
                return response()->json(['status' => false, 'invoice' => $item->order_id, 'errors' => 'This order only for Steadfast Courier']);
            } else if ($item->courier_tracking_code == NULL) {
                return response()->json(['status' => false, 'invoice' => $item->order_id, 'errors' => 'Steadfast Courier Tracking Code Not Found!']);
            } else {
                $response = Http::withHeaders([
                    'Api-Key' => $this->steadfast_api_key,
                    'Secret-Key' => $this->steadfast_secret_key,
                    'Content-Type' => 'application/json'
                ])->get($this->steadfast_api_base_url . '/status_by_trackingcode/' . $item->courier_tracking_code);

                $status = $response->json();
                if ($status && ($status['status'] ?? '') == '200' && ($status['delivery_status'] ?? null)) {
                    $item->courier_status = $status['delivery_status'];
                    if (!$item->save()) return response()->json(['status' => false, 'invoice' => $item->order_id, 'errors' => 'Something went wrong!']);
                } else {
                    return response()->json(['status' => false, 'invoice' => $item->order_id, 'errors' => 'Something went wrong!']);
                }
            }
        }

        return response()->json(['status' => true, 'msg' => 'Courier Status Updated Successfully!!']);
    }

    /** Manual assign (guarded) */
    public function assignUserStore()
    {
        try {
            $assignTo = (int) request('assign_user_id');
            $orderIds = (array) request('order_ids', []);

            $this->assertIsWorker($assignTo);

            DB::table('orders')->whereIn('id', $orderIds)->update(['assign_user_id' => $assignTo]);

            return response()->json(['status' => true, 'msg' => 'User Assigned!!']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function deleteAllOrder()
    {
        DB::beginTransaction();

        try {
            if (!auth()->user()->can('order.delete')) abort(403, 'unauthorized');

            $orders = DB::table('orders')->select('id')->whereIn('id', request('order_ids'))->get();

            foreach ($orders as $order) {
                $item = Order::find($order->id);

                if ($item->details()->count()) {
                    foreach ($item->details as $line) {
                        $this->util->increaseProductStock($line->product_id, $line->variation_id, $line->quantity);
                    }
                    $item->details()->delete();
                }

                if ($item->payments()->count()) $item->payments()->delete();

                $item->delete();
            }

            DB::commit();
            return response()->json(['status' => true, 'msg' => 'Order Is Deleted!!']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => false, 'msg' => $e->getMessage()]);
        }
    }

    /** -----------------------------
     *   Courier: REDX / PATHAO / STEADFAST
     *  ----------------------------- */

    private function markShipped(Order $order, int $courierId, array $extra = []): void
    {
        $order->courier_id = $order->courier_id ?: $courierId;
        $order->status = 'courier';
        foreach ($extra as $k => $v) {
            $order->{$k} = $v;
        }
        $order->save();
    }

    // Redx
    public function OrderSendToRedx()
    {
        foreach (request('order_ids') as $id) {
            $item = Order::with('user')->find($id);

            if (empty($item->courier_id)) $item->courier_id = 1; // Redx
            if ($item->courier_id != 1) return response()->json(['status' => false, 'msg' => 'Invoice: ' . $item->invoice_no . ' is not assigned to Redx']);
            if (!empty($item->courier_tracking_id)) return response()->json(['status' => false, 'msg' => 'Invoice: ' . $item->invoice_no . ' already sent to Redx']);

            $status = $this->createRedxParcel($item);

            if (!empty($status['tracking_id'])) {
                $this->markShipped($item, 1, ['courier_tracking_id' => $status['tracking_id']]);
            } elseif (!empty($status['message'])) {
                return response()->json(['status' => false, 'msg' => 'Invoice: ' . $item->invoice_no . ' ' . $status['message']]);
            } else {
                return response()->json(['status' => false, 'msg' => 'Invoice: ' . $item->invoice_no . ' unknown Redx response']);
            }
        }
        return response()->json(['status' => true, 'msg' => 'Order(s) sent to Redx & status updated!', 'reload' => true]);
    }

    public function getRedxAreaList($by = null, $value = null)
    {
        try {
            $response = Http::withHeaders(['API-ACCESS-TOKEN' => $this->redx_api_access_token])->get($this->redx_api_base_url . 'areas');
            $json = $response->json();
            return $json['areas'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function createRedxParcel($item)
    {
        $first = ($item->first_name ?? ($item->user->first_name ?? ''));
        $last  = ($item->last_name  ?? ($item->user->last_name  ?? ''));
        $name  = trim($first . ' ' . $last);
        $phone = ($item->mobile ?? ($item->user->mobile ?? ''));

        $response = Http::withHeaders([
            'API-ACCESS-TOKEN' => $this->redx_api_access_token,
            'Content-Type'     => 'application/json'
        ])->post($this->redx_api_base_url . 'parcel', [
            "customer_name"          => $name,
            "customer_phone"         => $phone,
            "delivery_area"          => $item->area_name,
            "delivery_area_id"       => $item->area_id,
            "customer_address"       => $item->shipping_address,
            "merchant_invoice_id"    => $item->invoice_no,
            "cash_collection_amount" => $item->final_amount,
            "parcel_weight"          => "500",
            "instruction"            => "",
            "value"                  => $item->final_amount,
            "pickup_store_id"        => 0,
            "parcel_details_json"    => []
        ]);

        return $response->json();
    }

    /** Pathao helpers (unchanged API shape) **/
    public function fetchAddressDetails(Request $request)
    {
        $address = strtolower($request->input('address'));
        $city = null;
        $zone = null;
        $area = null;

        $cities = $this->getPathaoCityList();
        $city = collect($cities)->first(function ($c) use ($address) {
            return str_contains($address, strtolower($c['city_name']));
        });

        if ($city) {
            $zonesResponse = $this->getPathaoZoneListByCity($city['city_id']);
            $zones = $zonesResponse->getData(true)['zones'] ?? [];
            $zone = collect($zones)->first(function ($z) use ($address) {
                return str_contains($address, strtolower($z['zone_name']));
            });

            if ($zone) {
                $areasResponse = $this->getPathaoAreaListByZone($zone['zone_id']);
                $areas = $areasResponse->getData(true)['areas'] ?? [];
                $area = collect($areas)->first(function ($a) use ($address) {
                    return str_contains($address, strtolower($a['area_name']));
                });
            }
        }

        return response()->json([
            'city_id' => $city['city_id'] ?? null,
            'zone_id' => $zone['zone_id'] ?? null,
            'area_id' => $area['area_id'] ?? null,
        ]);
    }

    public function getPathaoStoreList()
    {
        $info = Information::first();
        if (($info->pathao_status ?? 0) == 0) return [];
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . ($this->pathao_api_access_token ?? '')])
                ->get(($this->pathao_api_base_url ?? '') . 'aladdin/api/v1/stores');
            if ($response->failed()) {
                Log::error('Pathao Store API failed', ['response' => $response->body()]);
                return [];
            }
            return $response->json()['data']['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Pathao Store API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getPathaoCityList()
    {
        $info = Information::first();
        if (($info->pathao_status ?? 0) == 0) return [];
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . ($this->pathao_api_access_token ?? '')])
                ->get(($this->pathao_api_base_url ?? '') . 'aladdin/api/v1/countries/1/city-list');
            if ($response->failed()) {
                Log::error('Pathao City API failed', ['response' => $response->body()]);
                return [];
            }
            return $response->json()['data']['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Pathao City API exception', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function getPathaoZoneListByCity($city)
    {
        $info = Information::first();
        if (($info->pathao_status ?? 0) == 0) return response()->json(['success' => true, 'zones' => []]);
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . ($this->pathao_api_access_token ?? '')])
                ->get(($this->pathao_api_base_url ?? '') . 'aladdin/api/v1/cities/' . $city . '/zone-list');
            if ($response->failed()) {
                Log::error('Pathao Zone API failed', ['response' => $response->body()]);
                return response()->json(['success' => true, 'zones' => []]);
            }
            $zones = $response->json()['data']['data'] ?? [];
            return response()->json(['success' => true, 'zones' => $zones]);
        } catch (\Exception $e) {
            Log::error('Pathao Zone API exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => true, 'zones' => []]);
        }
    }

    public function getPathaoAreaListByZone($zone)
    {
        $info = Information::first();
        if (($info->pathao_status ?? 0) == 0) return response()->json(['success' => true, 'areas' => []]);
        try {
            $response = Http::withHeaders(['Authorization' => 'Bearer ' . ($this->pathao_api_access_token ?? '')])
                ->get(($this->pathao_api_base_url ?? '') . 'aladdin/api/v1/zones/' . $zone . '/area-list');
            if ($response->failed()) {
                Log::error('Pathao Area API failed', ['response' => $response->body()]);
                return response()->json(['success' => true, 'areas' => []]);
            }
            $areas = $response->json()['data']['data'] ?? [];
            return response()->json(['success' => true, 'areas' => $areas]);
        } catch (\Exception $e) {
            Log::error('Pathao Area API exception', ['message' => $e->getMessage()]);
            return response()->json(['success' => true, 'areas' => []]);
        }
    }

    public function OrderSendToPathao(Request $request)
    {
        DB::beginTransaction();
        try {
            foreach (request('order_ids') as $id) {
                $item = Order::with('user', 'details')->find($id);

                if (empty($item->courier_id)) $item->courier_id = 2; // Pathao
                if ($item->courier_id != 2) {
                    DB::rollBack();
                    return response()->json(['status' => 0, 'msg' => 'Invoice: ' . $item->invoice_no . ' is not assigned to Pathao']);
                }
                if (!empty($item->courier_tracking_id)) {
                    DB::rollBack();
                    return response()->json(['status' => 0, 'msg' => 'Invoice: ' . $item->invoice_no . ' already sent to Pathao']);
                }

                $status = $this->createPathaoParcel($item);

                if (!empty($status['data']['consignment_id'])) {
                    $this->markShipped($item, 2, [
                        'courier_status'      => $status['data']['order_status'] ?? null,
                        'courier_tracking_id' => $status['data']['consignment_id'],
                    ]);
                } elseif (!empty($status['errors'])) {
                    DB::rollBack();
                    return response()->json(['status' => 0, 'invoice' => $item->invoice_no, 'errors' => $status['errors']]);
                } else {
                    DB::rollBack();
                    return response()->json(['status' => 0, 'msg' => 'Invoice: ' . $item->invoice_no . ' unknown Pathao response']);
                }
            }

            DB::commit();
            return response()->json(['status' => 1, 'msg' => 'Order(s) sent to Pathao & status updated!', 'reload' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 0, 'msg' => $e->getMessage()]);
        }
    }

    /** NEW: Pathao parcel create method */
    public function createPathaoParcel($item)
    {
        $first = ($item->first_name ?? ($item->user->first_name ?? ''));
        $last  = ($item->last_name ?? ($item->user->last_name ?? ''));
        $name  = trim($first . ' ' . $last);
        $phone = ($item->mobile ?? ($item->user->mobile ?? ''));

        // মোট quantity
        $totalQty = 0;
        if ($item->relationLoaded('details')) {
            $totalQty = $item->details->sum('quantity');
        } else {
            $totalQty = $item->details()->sum('quantity');
        }
        if ($totalQty <= 0) $totalQty = 1;

        // Default weight (kg)
        $weight = $item->weight ? (float)$item->weight : 0.5;

        $payload = [
            "store_id"          => (int)$this->pathao_store_id,
            "merchant_order_id" => (string)$item->invoice_no,

            "recipient_name"    => $name,
            "recipient_phone"   => $phone,
            "recipient_address" => $item->shipping_address,

            // city/state/area তোমার ডাটাবেস অনুযায়ী adjust করো
            "city_id"           => (int)($item->city ?? 0),
            "zone_id"           => (int)($item->state ?? 0),
            "area_id"           => (int)($item->area_id ?? 0),

            "delivery_type"      => 48, // প্রয়োজন অনুযায়ী পরিবর্তন করো
            "item_type"          => 2,  // পার্সেল
            "item_quantity"      => $totalQty,
            "item_weight"        => $weight,
            "amount_to_collect"  => (float)$item->final_amount,
            "special_instruction"=> $item->note ?? '',
        ];

        try {
            Log::info('Pathao create order request', [
                'invoice' => $item->invoice_no,
                'payload' => $payload,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->pathao_api_access_token,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ])->post($this->pathao_api_base_url . 'aladdin/api/v1/orders', $payload);

            if ($response->failed()) {
                Log::error('Pathao create order failed', [
                    'invoice' => $item->invoice_no,
                    'body'    => $response->body(),
                    'status'  => $response->status(),
                ]);
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Pathao create order exception', [
                'invoice' => $item->invoice_no,
                'message' => $e->getMessage(),
            ]);
            return [
                'errors' => [
                    'exception' => $e->getMessage(),
                ],
            ];
        }
    }

    // Steadfast
    public function OrderSendToSteadfast()
    {
        foreach (request('order_ids') as $id) {
            $item = Order::with('user')->find($id);

            if (empty($item->courier_id)) $item->courier_id = 3; // Steadfast

            if ($item->courier_id != 3) return response()->json(['status' => false, 'invoice' => $item->invoice_no, 'errors' => 'This order is not assigned to Steadfast Courier']);

            if (!empty($item->courier_tracking_id)) return response()->json(['status' => false, 'invoice' => $item->invoice_no, 'errors' => 'This order already sent to Steadfast Courier']);

            $status = $this->createSteadfastParcel($item);

            Log::info('steadfast_status: '.json_encode($status));

            if (!empty($status['consignment']['consignment_id'])) {
                $this->markShipped($item, 3, [
                    'courier_tracking_id'   => $status['consignment']['consignment_id'],
                    'courier_tracking_code' => $status['consignment']['tracking_code'] ?? null,
                    'courier_status'        => $status['consignment']['status'] ?? null,
                ]);
            } else {
                return response()->json(['status' => false, 'invoice' => $item->invoice_no, 'errors' => 'Something went wrong!']);
            }
        }
        return response()->json(['status' => true, 'msg' => 'Order sent to Steadfast & status updated!', 'reload' => true]);
    }

    public function createSteadfastParcel($item)
    {
        $first = ($item->first_name ?? ($item->user->first_name ?? ''));
        $last  = ($item->last_name ?? ($item->user->last_name ?? ''));
        $name  = trim($first . ' ' . $last);
        $phone = ($item->mobile ?? ($item->user->mobile ?? ''));

        $apiUrl = $this->steadfast_api_base_url . '/create_order';
        
        try {
            $response = Http::timeout(30)->withHeaders([
                'Api-Key'     => $this->steadfast_api_key,
                'Secret-Key'  => $this->steadfast_secret_key,
                'Content-Type'=> 'application/json'
            ])->post($apiUrl, [
                "invoice"           => $item->invoice_no,
                "recipient_name"    => $name,
                "recipient_phone"   => $phone,
                "recipient_address" => $item->shipping_address,
                "cod_amount"        => (int) $item->final_amount,
                "note"              => $item->note,
            ]);

            // Log response details
            // Log::info('steadfast_response_status: ' . $response->status());
            // Log::info('steadfast_response_body: ' . $response->body());
            // Log::info('steadfast_response_headers: ' . json_encode($response->headers()));
            
            // Check if request was successful
            if ($response->successful()) {
                $jsonResponse = $response->json();
                Log::info('steadfast_response_json: ' . json_encode($jsonResponse));
                return $jsonResponse;
            } else {
                // Log error details
                Log::error('steadfast_api_error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'json' => $response->json()
                ]);
                return [
                    'error' => true,
                    'status' => $response->status(),
                    'message' => $response->body() ?: 'API request failed',
                    'json' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            Log::error('steadfast_api_exception: ' . $e->getMessage());
            Log::error('steadfast_api_exception_trace: ' . $e->getTraceAsString());
            return [
                'error' => true,
                'message' => $e->getMessage(),
                'exception' => get_class($e)
            ];
        }
    }

    public function viewAccessToken()
    {
        return view('backend.informations.generate-pathao-access-token');
    }

    public function generatePathaoAccessToken(Request $request)
    {
        $response = Http::withHeaders(['content-type' => 'application/json', 'accept' => 'application/json'])
            ->post($this->pathao_api_base_url . 'aladdin/api/v1/issue-token', [
                "client_id"     => $request->client_id,
                "client_secret" => $request->client_secret,
                "username"      => $request->client_email,
                "password"      => $request->client_password,
                "grant_type"    => "password"
            ]);

        $tokenData = $response->json();
        return view('backend.informations.generate-pathao-access-token-view', compact('tokenData'));
    }

    public function fraudulentCheck($mobileNo)
    {
        $info = Information::first();
        $dataList = Http::get('https://dash.hoorin.com/api/courier/search.php', [
            'apiKey' => $info->fraudApi,
            'searchTerm' => $mobileNo
        ]);
        $data = $dataList->json();
        return json_encode($data);
    }

    public function fraudOrderCheck($id)
    {
        $result = Order::select(['id', 'user_id'])->find($id);
        if ($result) {

            $result->customerPhone = $result->user ? $result->user->mobile : '01782889864';
            $totalSummery = $this->courierSummery($result->customerPhone);

            $datas = $totalSummery['Summaries'];
            $datas2 = $totalSummery['TotalSummary'];

            $customer = $result->user;
            if ($customer && isset($totalSummery['TotalSummary'])) {
                $customer->curier_summery = $datas2;
                $customer->save();
            }

            $datas2 = $datas2['Summaries'] ?? [];

            $result->total_parcels   = isset($datas2['Total Parcels']) ? $datas2['Total Parcels'] : 0;
            $result->total_delivered = isset($datas2['Total Delivered']) ? $datas2['Total Delivered'] : 0;
            $result->total_canceled  = isset($datas2['Total Canceled']) ? $datas2['Total Canceled'] : 0;
            $result->total_ratio     = ($result->total_parcels > 0) ? round(($result->total_delivered / $result->total_parcels) * 100, 0) : 0;
            $result->purcelsdatas    = count($datas) > 0 ? $datas : null;
        }

        $view = view('backend.orders.fraudOrder', compact('result'));
        return response($view);
    }

    public function courierSummery($number)
    {
        $info = Information::first();
        $apiKey = $info->fraudApi;

        $url1 = "https://dash.hoorin.com/api/courier/search.php?apiKey=$apiKey&searchTerm=$number";
        $url2 = "https://dash.hoorin.com/api/courier/sheet.php?apiKey=$apiKey&searchTerm=$number";

        $response1 = $this->callApi($url1);
        $response2 = $this->callApi($url2);

        $summary = [
            'Summaries'    => $response1['Summaries'] ?? [],
            'TotalSummary' => $response2,
        ];

        return $summary;
    }

    private function callApi($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
