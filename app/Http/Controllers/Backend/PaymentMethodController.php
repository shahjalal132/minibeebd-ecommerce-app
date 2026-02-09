<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;

class PaymentMethodController extends Controller
{
    public function index()
    {
        // Permission check can be added later if needed, for now similar to others
        // if(!auth()->user()->can('payment_method.view')) { abort(403, 'unauthorized'); }
        
        $items = PaymentMethod::all();
        return view('backend.payment_methods.index', compact('items'));
    }

    public function create()
    {
        return view('backend.payment_methods.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
             'name' => 'required',
             'number' => 'required',
             'instruction' => 'nullable',
             'type' => 'nullable',
             'status' => '',
        ]);

        $data['status'] = $request->status ?? 0;

        PaymentMethod::create($data);

        return response()->json(['status'=>true ,'msg'=>'Payment Method Created Successfully!!','url'=>route('admin.payment-methods.index')]);
    }

    public function edit($id)
    {
        $item = PaymentMethod::find($id);
        return view('backend.payment_methods.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $paymentMethod = PaymentMethod::find($id);
        
        $data = $request->validate([
             'name' => 'required',
             'number' => 'required',
             'instruction' => 'nullable',
             'type' => 'nullable',
        ]);
        
        $data['status'] = $request->status ?? 0;
           
        $paymentMethod->update($data);

        return response()->json(['status'=>true ,'msg'=>'Payment Method Updated Successfully!!','url'=>route('admin.payment-methods.index')]);
    }

    public function destroy($id)
    {
        $paymentMethod = PaymentMethod::find($id);
        $paymentMethod->delete();
        return response()->json(['status'=>true ,'msg'=>'Payment Method Deleted Successfully!!']);
    }
}
