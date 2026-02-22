<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderDetails;
use App\Models\DeliveryCharge;
use App\Utils\ModulUtil;
use App\Utils\Util;
use App\Models\CouponCode;
use App\Models\User;
use App\Models\Product;
use App\Models\Variation;
use App\Models\Information;
use App\Models\HoneyLandingPage;
use App\Models\PaymentMethod;
use App\Models\IncompleteOrder;
use App\Facades\FacebookConversion;

class CheckoutController extends Controller
{
    public $modulutil;
    public $util;

    public function __construct(ModulUtil $modulutil, Util $util)
    {

        $this->util = $util;
        $this->modulutil = $modulutil;
    }

    public function index()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('front.home');
        }

        $charges = DeliveryCharge::whereNotNull('status')->get();

        $coupon     = session()->get('coupon_discount');
        $coupn_item = CouponCode::where('amount', $coupon)->first();

        $cart  = session()->get('cart');
        $total = getCouponDiscount();

        try {
            $eventId = "IC_" . now()->format('Ymdhi');

            $contents   = [];
            $contentIds = [];
            $totalValue = 0;

            foreach ($cart as $item) {
                $contents[] = [
                    'id'         => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'item_price' => $item['price']
                ];
                $contentIds[] = $item['product_id'];
                $totalValue  += $item['price'] * $item['quantity'];
            }

            FacebookConversion::sendEvent('InitiateCheckout', [
                'currency'     => 'BDT',
                'value'        => $totalValue,
                'content_ids'  => $contentIds,
                'contents'     => $contents,
                'num_items'    => count($cart),
                'content_type' => 'product'
            ], $eventId);
        } catch (\Exception $e) {
            \Log::error('Facebook CAPI BeginCheckout Error: ' . $e->getMessage());
        }

        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        if (($coupn_item) && ($coupon > 0) && ($coupn_item->minimum_amount > $total)) {
            session()->put('coupon_discount', null);
            session()->put('discount_type', null);
        }

        return view('frontend.cart.checkout', compact('cart', 'charges', 'totalPrice'));
    }

    public function courierPercentage(Request $request)
    {
        $id     = $request->id;
        $number = $request->phone;

        if ($id) {
            $customer = User::findOrFail($id);

            if ($number) {
                $checkCourier = $this->callApi($number);
                if (isset($checkCourier)) {
                    $customer->curier_summery = $checkCourier;
                    $customer->save();
                }
            }
        }
    }

    private function callApi($number)
    {
        $info   = Information::first();
        $apiKey = $info->fraudApi;
        $url    = "https://dash.hoorin.com/api/courier/sheet.php?apiKey=$apiKey&searchTerm=$number";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function storelandData(Request $request)
    {
        $data = $request->validate([
            'mobile'             => 'digits_between:11,11',
            'first_name'         => 'required',
            'payment_method'     => '',
            'shipping_address'   => 'required',
            'note'               => '',
            'delivery_charge_id' => 'required|numeric',
            'final_amount'       => '',
            'amount'             => ''
        ]);

        if (empty(auth()->user()->id)) {
            $user = User::create([
                'first_name'       => $request->first_name,
                'mobile'           => $request->mobile,
                'shipping_address' => $request->shipping_address,
                'note'             => $request->note
            ]);
            $data['user_id'] = $user->id;
        } else {
            $data['user_id'] = auth()->user()->id;
        }

        $product = Product::with('variations')->where('id', $request->prd_id)->first();

        $quantity = $request->quantity;
        $proQty   = ($quantity == null || $quantity == '') ? 1 : $quantity;

        $total_discount_val = $proQty * $product['discount'];

        $pr_data = [
            'product_id'     => $request->prd_id,
            'quantity'       => $proQty,
            'unit_price'     => $request['amount'], // frontend থেকে আসা amount
            'discount'       => $product['discount'],
            'is_stock'       => $product['is_stock'],
            'purchase_price' => $product['purchase_prices'],
            'variation_id'   => $request['variation_id']
        ];

        $charge = DeliveryCharge::where('id', $data['delivery_charge_id'])->first();
        $charge = $charge ? $charge->amount : 0;
        $data['date'] = date('Y-m-d');

        // Order Assign Among Users Start
        $assign_user_id = 1;
        $users = User::whereHas('roles', function ($query) {
            $query->where('roles.name', 'Employee');
        })->where('status', 1)
            ->select('id')
            ->pluck('id')->toArray();

        $ordering = count($users) - 1;
        if (count($users) == 1) {
            $assign_user_id = $users[0];
            $data['assign_user_id'] = $assign_user_id;
        } else if ($ordering > 0) {
            $order  = Order::latest()->take($ordering)->get()->pluck('assign_user_id')->toArray();
            $output = array_merge(array_diff($order, $users), array_diff($users, $order));

            if (!empty($output)) {
                $assign_user_id = $output[0];
                $data['assign_user_id'] = $assign_user_id;
            } else {
                $data['assign_user_id'] = $assign_user_id;
            }
        }
        // Order Assign Among Users End.

        $data['invoice_no']      = rand(111111, 999999);
        $data['discount']        = $total_discount_val;
        $data['shipping_charge'] = $charge;
        $data['courier_id']      = 3;

        DB::beginTransaction();
        try {
            unset($data['payment_method']);

            $order = Order::create($data);

            if (!empty($pr_data)) {
                $order->details()->create($pr_data);
            }

            $this->modulutil->orderPayment($order, $request->all());
            $this->modulutil->orderstatus($order);

            $url = route('front.confirmOrderlanding', [$order->id]);
            session()->put('cart', []);
            session()->put('coupon_discount', null);
            session()->put('discount_type', null);

            DB::commit();
            return response()->json([
                'success' => true,
                'msg'     => 'Checkout Successfully..!!',
                'url'     => $url
            ]);
        } catch (\Exception $e) {

            DB::rollback();
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function incompleteStore(Request $request)
    {
        $req_data = $request->validate([
            'mobile' => 'required|numeric|min: 11',
            'name'   => 'nullable'
        ]);

        if (!empty($request->mobile)) {
            $user = User::updateOrCreate(
                ['mobile' => $request->mobile],
                [
                    'first_name' => $req_data['name'],
                    'username'   => strtolower(str_replace(' ', '', $req_data['name'])),
                    'status'     => 1,
                ]
            );

            $data['user_id'] = $user->id;
        }

        $checkIncomplete = Order::where('status', 'incomplete')->where('user_id', $user->id)->first();
        if ($checkIncomplete) {
            return response()->json(['message' => 'Already incomplete order stored']);
        }

        $carts          = session()->get('cart', []);
        $coupn_discount = getCouponDiscount();

        $product = [];
        if ($carts) {
            $total          = 0;
            $total_discount = 0;
            foreach ($carts as $key => $item) {
                $total          += $item['quantity'] * $item['price'];
                $total_discount += $item['quantity'] * $item['discount'];
                $product[] = [
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['price'],
                    'purchase_price' => $item['purchase_price'],
                    'variation_id'   => $item['variation_id'],
                    'discount'       => $item['discount'],
                    'is_stock'       => $item['is_stock'],
                ];
            }
        }

        $data['date'] = date('Y-m-d');

        $assign_user_id = 1;
        $users = User::whereHas('roles', function ($query) {
            $query->where('roles.name', 'worker');
        })->where('status', 1)
            ->select('id')
            ->pluck('id')->toArray();

        $ordering = count($users) - 1;
        if (count($users) == 1) {
            $assign_user_id = $users[0];
            $data['assign_user_id'] = $assign_user_id;
        } else if ($ordering > 0) {
            $order  = Order::latest()->take($ordering)->get()->pluck('assign_user_id')->toArray();
            $output = array_merge(array_diff($order, $users), array_diff($users, $order));

            if (!empty($output)) {
                $assign_user_id = $output[0];
                $data['assign_user_id'] = $assign_user_id;
            } else {
                $data['assign_user_id'] = $assign_user_id;
            }
        }

        $data['invoice_no']    = rand(111111, 999999);
        $data['discount']      = $total_discount + $coupn_discount;
        $data['amount']        = $total_discount + $total;
        $data['shipping_charge'] = 0;
        $data['first_name']    = $req_data['name'] ?? $user->name;
        $data['mobile']        = $req_data['mobile'];
        $data['shipping_address'] = $req_data['address'] ?? '';
        $data['status']        = 'incomplete';
        $data['final_amount']  = $total - $coupn_discount;

        DB::beginTransaction();
        try {
            unset($data['payment_method']);

            $order = Order::create($data);

            if (!empty($product)) {
                foreach ($product as $key => $item) {
                    if ($item['is_stock'] != 0) {
                        $stock = $this->util->checkProductStock($item['product_id'], $item['variation_id']);
                        if ($stock < $item['quantity']) {
                            return response()->json(['success' => false, 'msg' => ' Stock Note Available!']);
                        }
                        $this->util->decreaseProductStock($item['product_id'], $item['variation_id'], $item['quantity']);
                    }
                }
                $order->details()->createMany($product);
            }

            $this->modulutil->orderPayment($order, $request->all());
            $this->modulutil->orderstatus($order);
            DB::commit();
            return response()->json(['message' => 'Incomplete Order Store']);
        } catch (\Exception $e) {
            \Log::error('Someting Wrong: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mobile'             => 'digits_between:11,11',
            'first_name'         => 'required',
            'payment_method'     => 'required',
            'shipping_address'   => 'required',
            'ip_address'         => '',
            'note'               => '',
            'delivery_charge_id' => 'required|numeric',
        ]);

        if ($request->ip_address == null) {
            $data['ip_address'] = $request->ip;
        }

        $info         = Information::first();
        $limitMinutes = $info->time_limit ?? 60;

        // শুধু latest order fetch (future use এর জন্য)
        $latestByMobile = null;
        if (!empty($request->mobile)) {
            $latestByMobile = Order::where('mobile', $request->mobile)
                ->whereNot('status', 'incomplete')
                ->latest()
                ->first();
        }

        // Restriction check (same mobile / ip within X minutes)
        $appliesMobileCheck = ($info->is_mobile_check == 1) && !empty($request->mobile);
        $appliesIpCheck     = ($info->is_ip_check == 1) && !empty($data['ip_address']);

        if ($appliesMobileCheck || $appliesIpCheck) {
            $query = Order::whereNot('status', 'incomplete');

            $query->where(function ($q) use ($appliesMobileCheck, $appliesIpCheck, $request, $data) {
                $first = true;
                if ($appliesMobileCheck) {
                    $q->where('mobile', $request->mobile);
                    $first = false;
                }
                if ($appliesIpCheck) {
                    if ($first) {
                        $q->where('ip_address', $data['ip_address']);
                    } else {
                        $q->orWhere('ip_address', $data['ip_address']);
                    }
                }
            });

            $recentOrder = $query->where('created_at', '>=', now()->subMinutes($limitMinutes))
                ->latest()
                ->first();

            if ($recentOrder) {
                $minutesPassed = now()->diffInMinutes($recentOrder->created_at);
                $remaining     = max(0, $limitMinutes - $minutesPassed);
                return response()->json([
                    'success' => false,
                    'msg'     => "You can place a new order after {$remaining} minutes."
                ]);
            }
        }

        // user create/update by mobile
        if (!empty($request->mobile)) {
            $baseUsername = strtolower(str_replace(' ', '', $data['first_name']));
            $username     = $baseUsername;
            $counter      = 1;

            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter;
                $counter++;
            }

            $user = User::updateOrCreate(
                ['mobile' => $request->mobile],
                [
                    'first_name' => $data['first_name'],
                    'username'   => $username,
                    'status'     => 1,
                ]
            );

            $data['user_id'] = $user->id;
        }

        $carts          = session()->get('cart', []);
        $coupn_discount = getCouponDiscount();

        $product = [];
        if ($carts) {
            $total          = 0;
            $total_discount = 0;
            foreach ($carts as $key => $item) {
                $total          += $item['quantity'] * $item['price'];
                $total_discount += $item['quantity'] * $item['discount'];
                $product[] = [
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'unit_price'     => $item['price'],
                    'variation_id'   => $item['variation_id'],
                    'purchase_price' => $item['purchase_price'],
                    'discount'       => $item['discount'],
                    'is_stock'       => $item['is_stock'],
                ];
            }
        }

        $charge = DeliveryCharge::find($data['delivery_charge_id']);
        $charge = $charge ? $charge->amount : 0;
        $data['date'] = date('Y-m-d');

        // Order Assign Among Users Start
        $assign_user_id = 1;
        $users = User::whereHas('roles', function ($query) {
            $query->where('roles.name', 'worker');
        })->where('status', 1)
            ->select('id')
            ->pluck('id')->toArray();

        $ordering = count($users) - 1;
        if (count($users) == 1) {
            $assign_user_id = $users[0];
            $data['assign_user_id'] = $assign_user_id;
        } else if ($ordering > 0) {
            $order  = Order::latest()->take($ordering)->get()->pluck('assign_user_id')->toArray();
            $output = array_merge(array_diff($order, $users), array_diff($users, $order));

            if (!empty($output)) {
                $assign_user_id = $output[0];
                $data['assign_user_id'] = $assign_user_id;
            } else {
                $data['assign_user_id'] = $assign_user_id;
            }
        }

        $data['invoice_no']     = rand(111111, 999999);
        $data['discount']       = $total_discount + $coupn_discount;
        $data['amount']         = $total_discount + $total;
        $data['shipping_charge'] = $charge;
        $data['final_amount']   = $total + $charge - $coupn_discount;

        DB::beginTransaction();
        try {
            unset($data['payment_method']);

            $order = Order::where('status', 'incomplete')->where('user_id', $user->id)->first();

            if (!$order) {
                $data['status'] = 'pending';
                $order          = Order::create($data);

                if (!empty($product)) {
                    foreach ($product as $key => $item) {
                        $pro = Product::find($item['product_id']);
                        if ($pro->stock_quantity < $item['quantity']) {
                            return response()->json(['success' => false, 'msg' => ' Stock Note Available!']);
                        } else {
                            $pro->stock_quantity -= $item['quantity'];
                            $pro->save();
                        }
                    }
                    $order->details()->createMany($product);
                }
            } else {
                $order->details()->delete();
                $order->details()->createMany($product);

                foreach ($product as $key => $item) {
                    $pro = Product::find($item['product_id']);
                    if ($pro->stock_quantity < $item['quantity']) {
                        return response()->json(['success' => false, 'msg' => ' Stock Note Available!']);
                    } else {
                        $pro->stock_quantity -= $item['quantity'];
                        $pro->save();
                    }
                }

                $data['status']     = 'pending';
                $data['invoice_no'] = $order->invoice_no;
                $order->update($data);
            }

            $this->modulutil->orderPayment($order, $request->all());
            $this->modulutil->orderstatus($order);
            DB::commit();

            // Facebook CAPI Purchase
            try {
                $eventId = "PUR_" . $order->id;

                $contents   = [];
                $contentIds = [];

                foreach ($order->details as $sellProduct) {
                    $contents[] = [
                        'id'         => $sellProduct->product_id,
                        'quantity'   => $sellProduct->quantity,
                        'item_price' => $sellProduct->unit_price
                    ];
                    $contentIds[] = $sellProduct->product_id;
                }

                $customerEmail     = strtolower(trim($order->user->email ?? ''));
                $customerPhone     = preg_replace('/\D/', '', $order->mobile ?? '');
                $customerFirstName = strtolower(trim($order->first_name ?? ''));
                $externalId        = $order->user_id ?? null;

                $userData = [
                    'em'          => [$customerEmail ? hash('sha256', $customerEmail) : null],
                    'ph'          => [$customerPhone ? hash('sha256', $customerPhone) : null],
                    'fn'          => [$customerFirstName ? hash('sha256', $customerFirstName) : null],
                    'external_id' => [$externalId ? hash('sha256', $externalId) : null],
                ];

                FacebookConversion::sendPurchase([
                    'currency'      => 'BDT',
                    'value'         => $order->final_amount,
                    'content_ids'   => $contentIds,
                    'contents'      => $contents,
                    'order_id'      => $order->id,
                    'num_items'     => $order->details()->sum('quantity'),
                    'event_time'    => now()->timestamp,
                    'action_source' => 'website',
                    'user_data'     => $userData
                ], $eventId);

                \Log::info('Facebook CAPI Purchase tracked', [
                    'order_id' => $order->id,
                    'amount'   => $order->final_amount,
                    'items'    => count($contents)
                ]);
            } catch (\Exception $e) {
                \Log::error('Facebook CAPI Purchase Error: ' . $e->getMessage());
            }

            session()->put('cart', []);
            session()->put('coupon_discount', null);
            session()->put('discount_type', null);

            $msg    = 'You Got An Order';
            $number = $order->mobile;
            $success = SendSms($number, $msg);

            $url = route('front.confirmOrder', [$order->id]);
            return response()->json(['success' => true, 'msg' => 'Order Create successfully!', 'url' => $url]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    /**
     * ✅ UPDATED storeData – এখানে variant + after_discount অনুযায়ী unit_price/final_amount হিসাব হচ্ছে
     */
    public function storeData(Request $request)
    {
        $data = $request->validate([
            'mobile'             => 'digits_between:11,11',
            'first_name'         => 'required',
            'payment_method'     => '',
            'shipping_address'   => 'required',
            'note'               => '',
            'delivery_charge_id' => 'required|numeric',
            'prd_id'             => 'required|numeric',
            'variation_id'       => 'required|numeric',
            'quantity'           => 'nullable|numeric',
        ]);

        // User create / assign
        if (empty(auth()->user()->id)) {
            $user = User::create([
                'first_name'       => $request->first_name,
                'mobile'           => $request->mobile,
                'shipping_address' => $request->shipping_address,
                'note'             => $request->note
            ]);
            $data['user_id'] = $user->id;
        } else {
            $data['user_id'] = auth()->user()->id;
        }

        // Product & variation
        $product   = Product::with('variations')->where('id', $request->prd_id)->firstOrFail();
        $variation = Variation::find($request->variation_id);

        // Quantity
        $quantity = 1;
        if (!empty($request->quantity)) {
            $quantity = (int) $request->quantity;
        }

        // BASE PRODUCT PRICE (after_discount থাকলে আগে ওটা)
        $baseFinalPrice = (isset($product->after_discount) && $product->after_discount > 0)
            ? (float) $product->after_discount
            : (float) $product->sell_price;

        $finalPrice           = $baseFinalPrice;
        $basePriceForDiscount = (float) $product->sell_price;

        // VARIATION PRICE PRIORITY
        if ($variation) {
            if (!empty($variation->discount_price) && $variation->discount_price > 0) {
                $finalPrice           = (float) $variation->discount_price;
                $basePriceForDiscount = (float) $variation->price;
            } elseif (!empty($variation->price) && $variation->price > 0) {
                $finalPrice           = (float) $variation->price;
                $basePriceForDiscount = (float) $variation->price;
            }
        }

        // DISCOUNT AMOUNT
        $discountPerUnit = 0;
        if ($basePriceForDiscount > $finalPrice) {
            $discountPerUnit = $basePriceForDiscount - $finalPrice;
        }

        $totalDiscount = $discountPerUnit * $quantity;
        $subtotal      = $finalPrice * $quantity; // product total (shipping ছাড়া)

        // Order details row
        $pr_data = [
            'product_id'     => $product->id,
            'quantity'       => $quantity,
            'unit_price'     => $finalPrice,          // ✅ variant/after_discount price
            'discount'       => $discountPerUnit,     // প্রতি unit discount
            'is_stock'       => $product->is_stock,
            'purchase_price' => $product->purchase_prices,
            'variation_id'   => $variation ? $variation->id : null,
        ];

        // Delivery charge
        $charge = DeliveryCharge::find($data['delivery_charge_id']);
        $charge = $charge ? $charge->amount : 0;
        $data['date'] = date('Y-m-d');

        // Assign user (role_id 8)
        $usrs           = DB::table('model_has_roles')->where('role_id', 8)->get();
        $verified_users = [];

        foreach ($usrs as $u) {
            $test = DB::table('users')->where('id', $u->model_id)->first();
            if ($test && $test->status == 1) {
                $verified_users[] = $u->model_id;
            }
        }

        if (!empty($verified_users)) {
            $keyValue = array_rand($verified_users);
            $data['assign_user_id'] = $verified_users[$keyValue];
        } else {
            $data['assign_user_id'] = 1;
        }

        // Amount calculation
        $data['invoice_no']      = rand(111111, 999999);
        $data['discount']        = $totalDiscount;
        $data['amount']          = $subtotal + $totalDiscount; // চাইলে শুধু $subtotal করতে পারো
        $data['shipping_charge'] = $charge;
        $data['final_amount']    = $subtotal + $charge;

        DB::beginTransaction();
        try {
            unset($data['payment_method']);

            $order = Order::create($data);

            if (!empty($pr_data)) {
                $order->details()->create($pr_data);
            }

            $this->modulutil->orderPayment($order, $request->all());
            $this->modulutil->orderstatus($order);

            $url = route('front.confirmOrder', [$order->id]);
            session()->put('cart', []);
            session()->put('coupon_discount', null);
            session()->put('discount_type', null);

            // Optional SMS
            // $msg    = 'You Got An Order';
            // $number = $order->mobile;
            // $success= SendSms($number ,$msg);

            DB::commit();
            return response()->json([
                'success' => true,
                'msg'     => 'Checkout Successfully..!!',
                'url'     => $url,
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function StoreChk(Request $request)
    {

        $this->validate($request, [
            'first_name'         => 'required',
            'mobile'             => 'required',
            'shipping_address'   => 'required',
            'delivery_charge_id' => 'required'
        ]);

        $user = User::create([
            'first_name' => $request->input('firstname'),
            'last_name'  => $request->input('lastname'),
            'email'      => $request->input('email'),
            'mobile'     => $request->input('mobile'),
            'note'       => $request->input('note'),
        ]);

        $carts          = session()->get('cart', []);
        $coupn_discount = getCouponDiscount();

        $product = [];
        if ($carts) {
            $total          = 0;
            $total_discount = 0;
            foreach ($carts as $key => $item) {
                $total          += $item['quantity'] * $item['price'];
                $total_discount += $item['quantity'] * $item['discount'];
                $product[] = [
                    'product_id'   => $item['product_id'],
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['price'],
                    'variation_id' => $item['variation_id'],
                    'discount'     => $item['discount'],
                ];
            }
        }

        $data = [];

        $delivery_charge_id = $request->input('delivery_charge_id');
        $charge = DeliveryCharge::find($delivery_charge_id);
        $charge = $charge ? $charge->amount : 0;

        $data['date']    = date('Y-m-d');
        $data['user_id'] = $user->id;

        $usr = DB::table('model_has_roles')->where('role_id', 8)->inRandomOrder()->first();
        if ($usr) {
            $data['assign_user_id'] = $usr->model_id;
        } else {
            $data['assign_user_id'] = 1;
        }

        $data['invoice_no']       = time();
        $data['discount']         = $total_discount + $coupn_discount;
        $data['amount']           = $total_discount + $total;
        $data['delivery_charge_id'] = $request->input('delivery_charge_id');
        $data['shipping_charge']  = $charge;
        $data['final_amount']     = $total + $charge - $coupn_discount;

        $data['first_name']       = $request->input('firstname');
        $data['last_name']        = $request->input('lastname');
        $data['email']            = $request->input('email');
        $data['shipping_address'] = $request->input('shipping_address');
        $data['mobile']           = $request->input('mobile');
        $data['note']             = $request->input('note');

        DB::beginTransaction();
        try {
            unset($data['payment_method']);
            $order = Order::create($data);

            if (!empty($product)) {
                foreach ($product as $key => $item) {
                    $stock = $this->util->checkProductStock($item['product_id'], $item['variation_id']);
                    if ($stock < $item['quantity']) {
                        return response()->json(['success' => false, 'msg' => ' Stock Note Available!']);
                    }
                    $this->util->decreaseProductStock($item['product_id'], $item['variation_id'], $item['quantity']);
                }
                $order->details()->createMany($product);
            }

            $this->modulutil->orderPayment($order, $request->all());
            $this->modulutil->orderstatus($order);

            OrderPayment::create([
                'order_id'   => $order->id,
                'amount'     => $order->final_amount,
                'account_no' => $request->input('mobile'),
                'tnx_id'     => $request->input('tnx_id'),
                'method'     => 'paypal',
                'date'       => date('Y-m-d'),
                'note'       => ''
            ]);

            $order->payment_status = 'Paypal Completed';
            $order->save();

            DB::commit();

            session()->put('cart', []);
            session()->put('coupon_discount', null);
            session()->put('discount_type', null);

            $url = route('front.confirmOrder', [$order->id]);
            return response()->json(['success' => true, 'msg' => 'Order Create successfully!', 'url' => $url]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }

    public function getCouponDiscount(Request $request)
    {
        $data = $request->validate([
            'code' => 'required'
        ]);

        $cart  = session()->get('cart');
        $total = 0;
        if ($cart) {
            foreach ($cart as $id => $item) {
                $total += $item['price'] * $item['quantity'];
            }
        }

        $item = CouponCode::where('code', $request->code)
            ->where(function ($row) use ($total) {
                $row->where('minimum_amount', '0')
                    ->orWhereNull('minimum_amount')
                    ->orWhere('minimum_amount', '<=', $total);
            })
            ->whereDate('start', '<=', date('Y-m-d'))
            ->whereDate('end', '>=', date('Y-m-d'))->first();

        if ($item) {
            session()->put('coupon_discount', $item->amount);
            session()->put('discount_type', $item->discount_type);
            return response()->json(['success' => true, 'msg' => 'You Got Coupon Discount!']);
        } else {
            return response()->json(['success' => false, 'msg' => 'Not Found Any Coupon Discount!']);
        }
    }

    /**
     * Honey Template Guest Checkout
     * Handles checkout for honey landing page with guest users
     */
    public function honeyCheckout(Request $request)
    {
        // 1. Validation
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'phone' => 'required|digits:11',
            'address' => 'required|string',
            'delivery_charge_id' => 'required|integer|exists:delivery_charges,id',
            'payment_method' => 'required|string',
            'fromNumber' => 'required_unless:payment_method,cod|nullable|digits:11',
            'transactionId' => 'required_unless:payment_method,cod|nullable|string|max:100',
            'screenshot' => 'required_unless:payment_method,cod|nullable|image|mimes:jpg,png,jpeg,webp|max:5120'
        ]);

        // Additional validation for payment_method
        $paymentMethod = null;
        if ($data['payment_method'] !== 'cod') {
            $paymentMethod = PaymentMethod::where('id', $data['payment_method'])
                ->where('status', 1)
                ->first();
            if (!$paymentMethod) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Invalid payment method selected.'
                ], 422);
            }
        }

        // Validate delivery charge is active
        $deliveryCharge = DeliveryCharge::where('id', $data['delivery_charge_id'])
            ->where('status', 1)
            ->first();
        if (!$deliveryCharge) {
            return response()->json([
                'success' => false,
                'msg' => 'Invalid shipping option selected.'
            ], 422);
        }

        // 2. Get HoneyLandingPage active page
        $honeyPage = HoneyLandingPage::where('status', 1)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$honeyPage || !isset($honeyPage->content['product'])) {
            return response()->json([
                'success' => false,
                'msg' => 'Product information not available.'
            ], 422);
        }

        $productContent = $honeyPage->content['product'];

        DB::beginTransaction();
        try {
            // 3. Create/Update Guest User
            $user = User::updateOrCreate(
                ['mobile' => $data['phone']],
                [
                    'first_name' => $data['name'],
                    'shipping_address' => $data['address'],
                    'status' => 1
                ]
            );

            // 4. Resolve Product and Variation
            $product = null;
            $variation = null;
            $finalPrice = 0;
            $basePriceForDiscount = 0;
            $productId = null;
            $variationId = null;
            $quantity = 1;
            $isStock = 0;

            if (!empty($productContent['product_id'])) {
                // Dynamic Product
                $product = Product::where('id', $productContent['product_id'])
                    ->where('status', 1)
                    ->first();

                if (!$product) {
                    throw new \Exception('Product not available.');
                }

                $productId = $product->id;
                $isStock = $product->is_stock ?? 0;

                // Get first variation
                $variation = Variation::where('product_id', $product->id)->first();
                if ($variation) {
                    $variationId = $variation->id;
                }

                // #region agent log
                $logData2 = [
                    'id' => 'log_' . time() . '_' . uniqid(),
                    'timestamp' => time() * 1000,
                    'location' => 'CheckoutController.php:honeyCheckout',
                    'message' => 'Product model prices',
                    'data' => [
                        'product_sell_price' => $product->sell_price ?? null,
                        'product_after_discount' => $product->after_discount ?? null,
                    ],
                    'runId' => 'run1',
                    'hypothesisId' => 'A'
                ];
                file_put_contents($logFile, json_encode($logData2) . "\n", FILE_APPEND);
                // #endregion

                // Price calculation with priority: Honey page offer_price takes precedence
                // First check if honey page has offer_price or regular_price
                $honeyPageOfferPrice = !empty($productContent['offer_price']) ? (float) $productContent['offer_price'] : null;
                $honeyPageRegularPrice = !empty($productContent['regular_price']) ? (float) $productContent['regular_price'] : null;

                // Honey page prices have highest priority - use them if available
                if ($honeyPageOfferPrice !== null && $honeyPageOfferPrice > 0) {
                    // Use honey page offer_price (highest priority)
                    $finalPrice = $honeyPageOfferPrice;
                    $basePriceForDiscount = $honeyPageRegularPrice ?? (float) $product->sell_price;
                } elseif ($honeyPageRegularPrice !== null && $honeyPageRegularPrice > 0) {
                    // Use honey page regular_price if no offer_price
                    $finalPrice = $honeyPageRegularPrice;
                    $basePriceForDiscount = (float) $product->sell_price;
                } else {
                    // No honey page prices - check variation prices first, then product model
                    if ($variation) {
                        if (!empty($variation->discount_price) && $variation->discount_price > 0) {
                            $finalPrice = (float) $variation->discount_price;
                            $basePriceForDiscount = (float) $variation->price;
                        } elseif (!empty($variation->price) && $variation->price > 0) {
                            $finalPrice = (float) $variation->price;
                            $basePriceForDiscount = (float) $variation->price;
                        } else {
                            // Fall back to product model prices
                            $baseFinalPrice = (isset($product->after_discount) && $product->after_discount > 0)
                                ? (float) $product->after_discount
                                : (float) $product->sell_price;
                            $finalPrice = $baseFinalPrice;
                            $basePriceForDiscount = (float) $product->sell_price;
                        }
                    } else {
                        // No variation - use product model prices
                        $baseFinalPrice = (isset($product->after_discount) && $product->after_discount > 0)
                            ? (float) $product->after_discount
                            : (float) $product->sell_price;
                        $finalPrice = $baseFinalPrice;
                        $basePriceForDiscount = (float) $product->sell_price;
                    }
                }

                // #region agent log
                $logData3 = [
                    'id' => 'log_' . time() . '_' . uniqid(),
                    'timestamp' => time() * 1000,
                    'location' => 'CheckoutController.php:honeyCheckout',
                    'message' => 'Calculated finalPrice after honey page check (variation overwrite prevented)',
                    'data' => [
                        'honeyPageOfferPrice' => $honeyPageOfferPrice,
                        'honeyPageRegularPrice' => $honeyPageRegularPrice,
                        'hasVariation' => $variation ? true : false,
                        'variationPrice' => $variation ? ($variation->price ?? null) : null,
                        'variationDiscountPrice' => $variation ? ($variation->discount_price ?? null) : null,
                        'finalPrice' => $finalPrice,
                        'basePriceForDiscount' => $basePriceForDiscount,
                    ],
                    'runId' => 'post-fix-v2',
                    'hypothesisId' => 'A'
                ];
                file_put_contents($logFile, json_encode($logData3) . "\n", FILE_APPEND);
                // #endregion

                // Check stock if stock management is enabled
                if ($isStock && $variation) {
                    $availableStock = $this->util->checkProductStock($product->id, $variation->id);
                    if ($availableStock < $quantity) {
                        throw new \Exception('Stock not available!');
                    }
                }
            } else {
                // Static Product - use static data from content
                // Create static product data array
                $staticProductData = [
                    'title' => $productContent['title'] ?? 'MiniBee Honey Box',
                    'image' => $productContent['image'] ?? '',
                    'quantity' => $productContent['quantity'] ?? '৪ গ্রাম × ৫০টি স্যাচেট',
                    'regular_price' => !empty($productContent['regular_price']) ? (float) $productContent['regular_price'] : 550,
                    'offer_price' => !empty($productContent['offer_price']) ? (float) $productContent['offer_price'] : null,
                    'short_description' => $productContent['short_description'] ?? ''
                ];

                // Use static pricing
                $finalPrice = !empty($staticProductData['offer_price'])
                    ? $staticProductData['offer_price']
                    : $staticProductData['regular_price'];
                $basePriceForDiscount = $staticProductData['regular_price'];

                // For static products, we need a placeholder product_id for database constraint
                // Try to find a default product or use product_id = 1 as fallback
                $placeholderProduct = Product::where('status', 1)->first();
                if ($placeholderProduct) {
                    $productId = $placeholderProduct->id;
                    $product = $placeholderProduct;
                } else {
                    // If no products exist, we'll need to handle this differently
                    // For now, throw an error asking to create at least one product
                    throw new \Exception('No products available. Please create at least one product in the system.');
                }

                // Set variation to null for static products
                $variationId = null;
                $variation = null;
                $isStock = 0;
            }

            // Calculate discount
            $discountPerUnit = 0;
            if ($basePriceForDiscount > $finalPrice) {
                $discountPerUnit = $basePriceForDiscount - $finalPrice;
            }

            $totalDiscount = $discountPerUnit * $quantity;
            $subtotal = $finalPrice * $quantity;

            // #region agent log
            $logData4 = [
                'id' => 'log_' . time() . '_' . uniqid(),
                'timestamp' => time() * 1000,
                'location' => 'CheckoutController.php:honeyCheckout',
                'message' => 'Final price calculation',
                'data' => [
                    'finalPrice' => $finalPrice,
                    'subtotal' => $subtotal,
                    'totalDiscount' => $totalDiscount,
                    'shippingCharge' => $deliveryCharge->amount,
                ],
                'runId' => 'run1',
                'hypothesisId' => 'A'
            ];
            file_put_contents($logFile, json_encode($logData4) . "\n", FILE_APPEND);
            // #endregion

            // 5. Calculate Totals
            $shippingCharge = $deliveryCharge->amount;
            $finalAmount = $subtotal + $shippingCharge;

            // #region agent log
            $logData5 = [
                'id' => 'log_' . time() . '_' . uniqid(),
                'timestamp' => time() * 1000,
                'location' => 'CheckoutController.php:honeyCheckout',
                'message' => 'Order totals',
                'data' => [
                    'finalAmount' => $finalAmount,
                    'order_amount' => $subtotal, // Fixed: should be subtotal, not subtotal + discount
                    'totalDiscount' => $totalDiscount,
                ],
                'runId' => 'post-fix-v3',
                'hypothesisId' => 'A'
            ];
            file_put_contents($logFile, json_encode($logData5) . "\n", FILE_APPEND);
            // #endregion

            // 6. Assign Worker
            $usrs = DB::table('model_has_roles')->where('role_id', 8)->get();
            $verified_users = [];

            foreach ($usrs as $u) {
                $test = DB::table('users')->where('id', $u->model_id)->first();
                if ($test && $test->status == 1) {
                    $verified_users[] = $u->model_id;
                }
            }

            $assignUserId = 1;
            if (!empty($verified_users)) {
                $keyValue = array_rand($verified_users);
                $assignUserId = $verified_users[$keyValue];
            }

            // 7. Generate Invoice Number
            $invoiceNo = $this->generateUniqueInvoice();

            // 8. Create Order Record
            $orderData = [
                'user_id' => $user->id,
                'first_name' => $data['name'],
                'mobile' => $data['phone'],
                'shipping_address' => $data['address'],
                'delivery_charge_id' => $data['delivery_charge_id'],
                'shipping_charge' => $shippingCharge,
                'amount' => $subtotal, // Use subtotal (discounted price), not subtotal + discount
                'final_amount' => $finalAmount,
                'discount' => $totalDiscount,
                'status' => 'pending',
                'date' => date('Y-m-d'),
                'payment_status' => 'due',
                'invoice_no' => $invoiceNo,
                'assign_user_id' => $assignUserId
            ];

            $order = Order::create($orderData);

            // 9. Create OrderDetails Record
            $orderDetailsData = [
                'product_id' => $productId,
                'variation_id' => $variationId,
                'quantity' => $quantity,
                'unit_price' => $finalPrice,
                'discount' => $discountPerUnit,
                'is_stock' => $isStock
            ];

            if ($product) {
                $orderDetailsData['purchase_price'] = $product->purchase_price ?? 0;
            }

            $order->details()->create($orderDetailsData);

            // Decrease stock if stock management is enabled
            if ($isStock && $product && $variation) {
                $this->util->decreaseProductStock($product->id, $variation->id, $quantity);
            }

            // 10. Handle Payment
            if ($data['payment_method'] !== 'cod' && $paymentMethod) {
                // Handle screenshot upload
                $screenshotPath = null;
                if ($request->hasFile('screenshot')) {
                    $screenshot = $request->file('screenshot');
                    $fileName = time() . '_' . $screenshot->getClientOriginalName();
                    $uploadPath = public_path('payment_screenshots');

                    if (!file_exists($uploadPath)) {
                        mkdir($uploadPath, 0755, true);
                    }

                    $screenshot->move($uploadPath, $fileName);
                    $screenshotPath = 'payment_screenshots/' . $fileName;
                }

                // Create OrderPayment record
                $order->payments()->create([
                    'amount' => $order->final_amount,
                    'account_no' => $data['fromNumber'],
                    'tnx_id' => $data['transactionId'],
                    'method' => $paymentMethod->name,
                    'date' => date('Y-m-d'),
                    'note' => $screenshotPath ? 'Screenshot: ' . $screenshotPath : ''
                ]);

                // Update payment status
                $order->payment_status = strtolower($paymentMethod->name) . '_pending';
                $order->save();
            }

            // 11. Update Order Status
            $this->modulutil->orderPayment($order, $request->all());
            $this->modulutil->orderstatus($order);

            DB::commit();

            // 12. Delete incomplete order if exists
            try {
                $sessionId = session()->getId();
                $ipAddress = $request->ip();
                IncompleteOrder::bySessionAndIp($sessionId, $ipAddress)->delete();
            } catch (\Exception $e) {
                \Log::error('Failed to delete incomplete order: ' . $e->getMessage());
                // Don't fail the order creation if incomplete order deletion fails
            }

            // 13. Return Success Response
            $url = route('front.confirmOrderlanding', $order->id);

            return response()->json([
                'success' => true,
                'msg' => 'Order Create successfully!',
                'url' => $url,
                'order_id' => $order->id
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'msg' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateUniqueInvoice(): string
    {
        do {
            $candidate = (string) random_int(111111, 999999);
        } while (Order::where('invoice_no', $candidate)->exists());
        return $candidate;
    }

    /**
     * Save incomplete order data as user types
     */
    public function saveIncompleteOrder(Request $request)
    {
        try {
            $sessionId = session()->getId();
            $ipAddress = $request->ip();

            $data = [
                'session_id' => $sessionId,
                'ip_address' => $ipAddress,
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
                'delivery_charge_id' => $request->input('delivery_charge_id'),
                'payment_method' => $request->input('payment_method'),
                'from_number' => $request->input('fromNumber'),
                'transaction_id' => $request->input('transactionId'),
            ];

            // Handle screenshot upload if provided
            if ($request->hasFile('screenshot')) {
                $screenshot = $request->file('screenshot');
                $fileName = time() . '_' . $screenshot->getClientOriginalName();
                $uploadPath = public_path('incomplete_order_screenshots');

                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }

                $screenshot->move($uploadPath, $fileName);
                $data['screenshot_path'] = 'incomplete_order_screenshots/' . $fileName;
            }

            // Use updateOrCreate with session_id and ip_address as unique identifiers
            IncompleteOrder::updateOrCreate(
                [
                    'session_id' => $sessionId,
                    'ip_address' => $ipAddress
                ],
                $data
            );

            return response()->json([
                'success' => true,
                'msg' => 'Incomplete order saved'
            ]);
        } catch (\Exception $e) {
            // Fail silently to not disrupt user experience
            \Log::error('Failed to save incomplete order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Failed to save incomplete order'
            ], 500);
        }
    }
}
