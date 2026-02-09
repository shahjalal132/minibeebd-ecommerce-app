<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Variation;
use App\Facades\FacebookConversion;
use App\Utils\Util;

class CartController extends Controller
{
    protected $util;

    public function __construct(Util $util)
    {
        $this->util = $util;
    }

    /**
     * ------------------------
     * Helper: price calculation
     * ------------------------
     *
     * priority:
     *  1) variation->discount_price (থাকলে)
     *  2) variation->price + product-level discount থাকলে, সেই অনুপাত
     *  3) product->after_discount (থাকলে)
     *  4) না থাকলে product->sell_price
     *
     * return: [finalPrice, discountPerUnit, basePrice]
     */
    private function calculatePrice(Product $product, ?Variation $variation): array
    {
        $basePrice    = (float) $product->sell_price; // মূল দাম
        $finalPrice   = $basePrice;                   // শুরুতে ধরি কোনো ডিসকাউন্ট নেই
        $productAfter = (float) ($product->after_discount ?? 0);

        // product-level discount আছে কি?
        $hasProductDiscount = $productAfter > 0 && $productAfter < $product->sell_price;

        // 1) variation discount_price থাকলে সর্বোচ্চ priority
        if ($variation && $variation->discount_price && $variation->discount_price > 0) {
            $basePrice  = (float) ($variation->price > 0 ? $variation->price : $product->sell_price);
            $finalPrice = (float) $variation->discount_price;
        }
        // 2) variation price আছে (size অনুযায়ী দাম আলাদা) কিন্তু discount_price নেই
        elseif ($variation && $variation->price && $variation->price > 0) {
            $basePrice = (float) $variation->price;

            if ($hasProductDiscount && $product->sell_price > 0) {
                // product এ যদি 20% ডিসকাউন্ট থাকে, সেই অনুপাতে variation price এও ডিসকাউন্ট
                $discountRatio = ($product->sell_price - $productAfter) / $product->sell_price; // যেমন 0.20
                $finalPrice    = round($basePrice * (1 - $discountRatio));
            } else {
                $finalPrice = $basePrice;
            }
        }
        // 3) কোনো variation নাই বা variation price/discount নাই → product-level discount প্রযোজ্য
        else {
            if ($hasProductDiscount) {
                $finalPrice = $productAfter;
            } else {
                $finalPrice = $basePrice;
            }
        }

        $discountPerUnit = max(0, $basePrice - $finalPrice);

        return [$finalPrice, $discountPerUnit, $basePrice];
    }

    public function index()
    {
        $cart = session()->get('cart', []);

        if (request()->ajax()) {

            $segm = request()->segment(1) ?? 'home';

            $view = view('frontend.partials.cart_sidebar', compact('cart','segm'))->render();

            return response()->json(['success'=>true,'html'=>$view]);
        }

        return view('frontend.cart.index', compact('cart'));
    }

    /**
     * কার্টে যোগ করার মেথড (AJAX storeCart রুট)
     */
    public function storeCart(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|numeric',
            'variation_id' => 'required|numeric',
        ]);

        $segm = request()->segment(1) ?? 'home';

        $product_id   = (int) $request->product_id;
        $variation_id = (int) $request->variation_id;
        $quantity     = (int) ($request->quantity ?? 1);

        $product   = Product::findOrFail($product_id);
        $variation = Variation::find($variation_id);

        // ✅ price & discount হিসাব
        [$finalPrice, $discountPerUnit, $basePrice] = $this->calculatePrice($product, $variation);

        $cart = session()->get('cart', []);

        // quantity valid কিনা চেক
        if ($quantity <= 0) {
            return response()->json(['success' => false, 'msg' => 'Please Select Minimum 1 Quantity']);
        }

        if ($product->stock_quantity < $quantity) {
            return response()->json(['success'=>false,'msg'=>' Stock Not Available!']);
        }

        // ইতিমধ্যে কার্টে ওই ভ্যারিয়েন্ট থাকলে quantity বাড়াও
        if (isset($cart[$variation_id])) {

            $newQty = $cart[$variation_id]['quantity'] + $quantity;

            if ($request->is_stock != 0 && $product->stock_quantity < $newQty) {
                return response()->json(['success'=>false,'msg'=>' Stock Not Available!']);
            }

            $cart[$variation_id]['quantity'] = $newQty;
            $cart[$variation_id]['price']    = $finalPrice;      // ✅ variation / discount আপডেট
            $cart[$variation_id]['discount'] = $discountPerUnit; // ✅ প্রতি ইউনিট ডিসকাউন্ট
            $cart[$variation_id]['original_price'] = $basePrice;

        } else {

            $cart[$variation_id] = [
                "name"            => $product->name,
                "size"            => $variation && $variation->size ? $variation->size->title : '',
                "color"           => $variation && $variation->color ? $variation->color->name : '',
                "quantity"        => $quantity,
                "price"           => $finalPrice,        // ✅ final price
                "discount"        => $discountPerUnit,   // ✅ প্রতি unit discount
                "original_price"  => $basePrice,         // (just for info/debug)
                "variation_id"    => $variation_id,
                "product_id"      => $product_id,
                "category_name"   => $product->category->name ?? '',
                "purchase_price"  => $product->purchase_prices,
                "image"           => $product->image,
                "is_stock"        => $product->is_stock,
                "is_free_shipping"=> $product->is_free_shipping
            ];
        }

        session()->put('cart', $cart);

        // ----- Facebook CAPI AddToCart -----
        try {
            $eventId = "ATC_" . now()->format('Ymdhi');

            FacebookConversion::sendAddToCart([
                'content_ids' => [$product->id],
                'value'       => $finalPrice * $quantity,
                'currency'    => 'BDT',
                'contents'    => [
                    [
                        'id'         => $product->id,
                        'quantity'   => $quantity,
                        'item_price' => $finalPrice,
                    ]
                ]
            ], $eventId);

        } catch (\Exception $e) {
            \Log::error('Facebook CAPI AddToCart Error: ' . $e->getMessage());
        }

        $view         = view('frontend.partials.cart_sidebar',compact('cart','segm'))->render();
        $total_item   = getTotalCart();
        $total_amount = getTotalAmount();

        $url = $request->action_type == 'cart'
            ? ''
            : route('front.checkouts.index');

        return response()->json([
            'success' => true,
            'msg'     => 'Product added to cart successfully!',
            'html'    => $view,
            'item'    => $total_item,
            'amount'  => $total_amount,
            'url'     => $url
        ]);
    }

    /**
     * অন্য জায়গা থেকে store() ব্যবহার করলে (প্রায় same লজিক)
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'   => 'required|numeric',
            'variation_id' => 'required|numeric',
        ]);

        $segm = request()->segment(1) ?? 'home';

        $product_id   = (int) $request->product_id;
        $variation_id = (int) $request->variation_id;
        $quantity     = (int) ($request->quantity ?? 1);

        $product   = Product::findOrFail($product_id);
        $variation = Variation::find($variation_id);

        [$finalPrice, $discountPerUnit, $basePrice] = $this->calculatePrice($product, $variation);

        $cart = session()->get('cart', []);

        if ($quantity <= 0) {
            return response()->json(['success' => false, 'msg' => 'Please Select Minimum 1 Quantity']);
        }

        if ($product->stock_quantity < $quantity) {
            return response()->json(['success'=>false,'msg'=>' Stock Not Available!']);
        }

        if (isset($cart[$variation_id])) {

            $newQty = $cart[$variation_id]['quantity'] + $quantity;

            $cart[$variation_id]['quantity']       = $newQty;
            $cart[$variation_id]['variation_id']   = $variation_id;
            $cart[$variation_id]['price']          = $finalPrice;
            $cart[$variation_id]['discount']       = $discountPerUnit;
            $cart[$variation_id]['original_price'] = $basePrice;

        } else {

            $cart[$variation_id] = [
                "name"            => $product->name,
                "size"            => $variation && $variation->size ? $variation->size->title : '',
                "color"           => $variation && $variation->color ? $variation->color->name : '',
                "quantity"        => $quantity,
                "purchase_price"  => $product->purchase_prices,
                "price"           => $finalPrice,
                "discount"        => $discountPerUnit,
                "original_price"  => $basePrice,
                "variation_id"    => $variation_id,
                "product_id"      => $product_id,
                "category_name"   => $product->category->name ?? '',
                "image"           => $product->image,
                "is_stock"        => $product->is_stock,
                "is_free_shipping"=> $product->is_free_shipping
            ];
        }

        session()->put('cart', $cart);

        $view       = view('frontend.partials.cart_sidebar',compact('cart','segm'))->render();
        $total_item = getTotalCart();
        $url        = route('front.checkouts.index');

        return response()->json([
            'success' => true,
            'msg'     => 'Product added to cart successfully!',
            'html'    => $view,
            'item'    => $total_item,
            'url'     => $url
        ]);
    }

    public function edit(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['success'=>false,'msg'=>' Something Went Wrong !']);
        }

        $qty  = (int) $request->quantity;
        $cart = session()->get('cart', []);

        if (!isset($cart[$id])) {
            return response()->json(['success'=>false,'msg'=>' Item not found in cart!']);
        }

        $segm = $request->segment ? $request->segment : 'home';

        if ($qty <= 0) {
            unset($cart[$id]);
        } else {
            $product_id = $cart[$id]["product_id"];
            $is_stock   = $cart[$id]["is_stock"];
            $stock      = $this->util->checkProductStock($product_id, $id);

            if ($is_stock == 1 && $stock < $qty) {
                return response()->json(['success'=>false,'msg'=>' Stock Not Available!']);
            }

            $cart[$id]["quantity"] = $qty;
        }

        session()->put('cart', $cart);

        // totalPrice এখন update হওয়া cart থেকে
        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        $view  = view('frontend.partials.cart_sidebar',compact('cart','segm'))->render();
        $view2 = view('frontend.cart.details')->render();
        $view3 = view('frontend.cart.other_details',compact('totalPrice'))->render();

        return response()->json([
            'success'=>true,
            'msg'=>'Update cart successfully!',
            'html'=>$view,
            'html2'=>$view2,
            'html3'=>$view3,
            'segment'=>$segm
        ]);
    }

    public function destroy($id)
    {
        if (!$id) {
            return response()->json(['success'=>false,'msg'=>' Something Went Wrong !']);
        }

        $segm = request()->segment(1) ?? 'home';

        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        $totalPrice = 0;
        foreach ($cart as $item) {
            $totalPrice += $item['price'] * $item['quantity'];
        }

        $view       = view('frontend.partials.cart_sidebar',compact('cart','segm'))->render();
        $view2      = view('frontend.cart.details')->render();
        $view3      = view('frontend.cart.other_details',compact('totalPrice'))->render();
        $total_item = getTotalCart();
        $url        = route('front.home');

        return response()->json([
          'success'=>true,
          'msg'=>'Product removed successfully !',
          'html'=>$view,
          'html2'=>$view2,
          'html3'=>$view3,
          'item'=>$total_item,
          'segment'=>$segm,
          'url'=>$url,
        ]);
    }

    public function clearAll()
    {
        session()->put('cart', []);
        return redirect()->route('front.home');
    }
}
