<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IncompleteOrder;
use App\Models\DeliveryCharge;

class IncompleteOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!auth()->user()->can('incomplete_order.view')) {
            abort(403, 'unauthorized');
        }

        $q = $request->q;
        $query = IncompleteOrder::with('deliveryCharge')->latest();

        if (!empty($q)) {
            $query->where(function ($row) use ($q) {
                $row->where('name', 'like', '%' . $q . '%')
                    ->orWhere('phone', 'like', '%' . $q . '%')
                    ->orWhere('address', 'like', '%' . $q . '%')
                    ->orWhere('session_id', 'like', '%' . $q . '%')
                    ->orWhere('ip_address', 'like', '%' . $q . '%');
            });
        }

        $items = $query->paginate(30)->appends($request->all());

        if ($request->ajax()) {
            return view('backend.incomplete_orders.received_order', compact('items'))->render();
        }

        return view('backend.incomplete_orders.index', compact('items', 'q'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (!auth()->user()->can('incomplete_order.view')) {
            abort(403, 'unauthorized');
        }

        $item = IncompleteOrder::with('deliveryCharge')->findOrFail($id);
        return view('backend.incomplete_orders.show', compact('item'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!auth()->user()->can('incomplete_order.delete')) {
            abort(403, 'unauthorized');
        }

        $item = IncompleteOrder::findOrFail($id);
        
        // Delete screenshot file if exists
        if ($item->screenshot_path && file_exists(public_path($item->screenshot_path))) {
            unlink(public_path($item->screenshot_path));
        }
        
        $item->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Incomplete order deleted successfully!'
        ]);
    }
}
