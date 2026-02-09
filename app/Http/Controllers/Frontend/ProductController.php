<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Combo;
use App\Models\Product;
use App\Models\Category;
use App\Models\Type;
use App\Models\Size;
use App\Models\Information;
use App\Models\LandingPage;
use App\Models\DeliveryCharge;
use App\Facades\FacebookConversion;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        // ===== AJAX (list data) =====
        if ($request->ajax()) {
            $q        = (array) $request->input('q');
            $type_id  = (array) $request->input('brand_id');
            $cat_id   = (array) $request->input('cat_id');
            $size_id  = (array) $request->input('size_id');
            $shorting = $request->input('shorting');

            // বেস কুয়েরি (status=1 + প্রাধান্য সোর্ট)
            $query = Product::query()
                ->with('variation')
                ->select('products.*')
                ->where('products.status', 1)
                ->orderByRaw('IF(products.priority IS NULL, 1, 0), products.priority ASC');

            // ক্যাটাগরি ফিল্টার
            if (!empty($cat_id)) {
                $query->whereIn('products.category_id', $cat_id);
            }

            // ব্র্যান্ড/টাইপ ফিল্টার
            if (!empty($type_id)) {
                $query->whereIn('products.type_id', $type_id);
            }

            // সাইজ ফিল্টার: শুধু তখনই JOIN + GROUP BY
            if (!empty($size_id)) {
                $query->leftJoin('variations as v', 'v.product_id', '=', 'products.id')
                      ->whereIn('v.size_id', $size_id)
                      ->groupBy('products.id'); // ✅ কেবল size ফিল্টারে
            }

            // সার্চ (name/description)
            if (!empty($q)) {
                // যদি string আসে, array বানিয়ে নেই
                $term = is_array($q) ? implode(' ', $q) : $q;
                $query->where(function ($row) use ($term) {
                    $row->where('products.name', 'like', "%{$term}%")
                        ->orWhere('products.description', 'like', "%{$term}%");
                });
            }

            // শর্টিং
            if (!empty($shorting)) {
                if ($shorting === 'desc') {
                    $query->orderBy('products.id', 'desc');
                } elseif ($shorting === 'asc') {
                    $query->orderBy('products.id', 'asc');
                } elseif ($shorting === 'name') {
                    $query->orderBy('products.name', 'asc');
                } elseif ($shorting === 'price_low') {
                    $query->orderBy('products.sell_price', 'asc');
                } elseif ($shorting === 'price_high') {
                    $query->orderBy('products.sell_price', 'desc');
                }
            }

            // ❌ এখানে কোনো groupBy নেই (উপরের size ফিল্টার ব্লকে আছে)
            $items = $query->paginate(30);

            return view('frontend.products.index_data', compact('items'))->render();
        }

        // ===== প্রথম পেজ লোড =====
        $types = Type::orderBy('name')->get();
        $cats  = Category::whereNull('parent_id')->get();
        $sizes = Size::all();

        return view('frontend.products.index', compact('cats', 'sizes', 'types'));
    }

    public function comboProducts()
    {
        $items = Combo::with('product')->paginate(30);
        return view('frontend.products.combo', compact('items'));
    }

    public function show($id)
    {
        $recent_product = session()->get('recent_product', []);
        if (!in_array($id, $recent_product)) {
            $recent_product[] = $id;
            session()->put('recent_product', $recent_product);
        }

        $singleProduct = Product::with([
            'sizes',
            'reviews' => function ($q) { $q->where('status', 1); }
        ])->find($id);

        // Facebook CAPI (try/catch safe)
        try {
            $eventId    = "SV_{$singleProduct->id}_" . now()->format('ymdhi');
            $finalPrice = ($singleProduct->after_discount && $singleProduct->after_discount > 0)
                ? $singleProduct->after_discount
                : $singleProduct->sell_price;

            $userData = [
                'em'          => [hash('sha256', strtolower(trim(auth()->user()->email ?? '')))],
                'ph'          => [hash('sha256', preg_replace('/\D/', '', auth()->user()->phone_number ?? ''))],
                'fn'          => [hash('sha256', strtolower(trim(auth()->user()->name ?? '')))],
                'external_id' => [hash('sha256', auth()->user()->id ?? '')],
            ];

            FacebookConversion::sendViewContent([
                'product_id'       => $singleProduct->id,
                'product_name'     => $singleProduct->name,
                'value'            => $finalPrice,
                'currency'         => 'BDT',
                'content_type'     => 'product',
                'content_category' => $singleProduct->category->name ?? 'Unknown Category',
                'event_time'       => now()->timestamp,
                'action_source'    => 'website',
                'user_data'        => $userData
            ], $eventId);
        } catch (\Exception $e) {
            \Log::error('Facebook CAPI Error: ' . $e->getMessage());
        }

        $products = Product::where('id', '!=', $id)
            ->where('category_id', $singleProduct->category_id)
            ->where('status', 1)
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->take(6)
            ->get();

        $charges = DeliveryCharge::all();

        return view('frontend.products.show', compact('singleProduct', 'products', 'charges'));
    }

    public function relativeProduct($id)
    {
        $product = Product::with('sizes', 'sizes.stocks')->find($id);

        $products = Product::with('variation')
            ->select('products.*')
            ->where('products.category_id', $product->category_id)
            ->whereNotIn('products.id', [$id])
            ->where('products.status', 1)
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->take(12)
            ->get();

        $view = view('frontend.products.partials.relative_product', compact('products'))->render();
        return response()->json(['success' => true, 'html' => $view]);
    }

    public function trendingProduct()
    {
        $info            = Information::first();
        $newarrival_num  = $info->newarrival_num;

        $products = Product::with('variation')
            ->whereNull('products.discount_type')
            ->where('products.status', 1)
            ->select('products.*')
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->latest()
            ->take($newarrival_num)
            ->get();

        $view = view('frontend.products.partials.trending_product', compact('products'))->render();
        return response()->json(['success' => true, 'html' => $view]);
    }

    public function hotdealProduct()
    {
        $info         = Information::first();
        $discount_num = $info->discount_num;

        $products = Product::with('variation')
            ->whereNotNull('products.discount_type')
            ->where('products.status', 1)
            ->select('products.*')
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->take($discount_num)
            ->get();

        $view = view('frontend.products.partials.hotdeal_product', compact('products'))->render();
        return response()->json(['success' => true, 'html' => $view]);
    }

    public function recommendedProduct()
    {
        $info      = Information::first();
        $recom_num = $info->recommend_num;

        $products = Product::with('variation')
            ->where('products.status', 1)
            ->where('products.is_recommended', 1)
            ->select('products.*')
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->take($recom_num)
            ->get();

        $view = view('frontend.products.partials.recommended_product', compact('products'))->render();
        return response()->json(['success' => true, 'html' => $view]);
    }

    public function discountProduct(Request $request)
    {
        if ($request->ajax()) {
            $items = Product::with('variation')
                ->whereNotNull('products.discount_type')
                ->where('products.status', 1)
                ->select('products.*')
                ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
                ->latest()
                ->paginate(24);

            $view = view('frontend.products.partials.discount', compact('items'))->render();
            return response()->json(['success' => true, 'html' => $view]);
        }

        return view('frontend.products.discount');
    }

    public function brands()
    {
        $items = Type::orderBy('name')->get();
        return view('frontend.brands', compact('items'));
    }

    public function landing_page($id)
    {
        $ln_pg   = LandingPage::with('images')->find($id);
        $title   = $ln_pg->title1;
        $charges = DeliveryCharge::whereNotNull('status')->get();

        return view('backend.landing_pages.land_page', compact('ln_pg', 'charges', 'title'));
    }

    public function landing_pages_two($id)
    {
        $ln_pg   = LandingPage::with('images')->find($id);
        $title   = $ln_pg->title1;
        $charges = DeliveryCharge::whereNotNull('status')->get();

        return view('backend.landing_pages.land_page_two', compact('ln_pg', 'charges', 'title'));
    }

    public function subCategories($slug)
    {
        $cat  = Category::where('url', $slug)->first();
        $q    = Category::whereNotNull('parent_id');
        if ($cat) {
            $q->where('parent_id', $cat->id);
        }
        $subs = $q->get();

        return view('frontend.sub_categories', compact('subs'));
    }

    public function subCategories1($slug)
    {
        $cat    = Category::where('url', $slug)->first();
        $cat_id = $cat['id'];

        $items = Product::with('variation')
            ->where('products.category_id', $cat_id)
            ->where('products.status', 1)
            ->select('products.*')
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->paginate(30);

        $types = Type::orderBy('name')->get();
        $cats  = Category::whereNull('parent_id')->get();
        $sizes = Size::all();

        return view('frontend.products.another_index', compact('items', 'types', 'cats', 'sizes', 'cat'));
    }

    public function subsubCategories($slug)
    {
        $s_cat  = Category::where('url', $slug)->first();
        $scat_id = $s_cat['id'];

        $items = Product::with('variation')
            ->where('products.sub_category_id', $scat_id)
            ->where('products.status', 1)
            ->select('products.*')
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->paginate(30);

        $types = Type::orderBy('name')->get();
        $cats  = Category::whereNull('parent_id')->get();
        $sizes = Size::all();

        return view('frontend.products.another_sub_index', compact('items', 'types', 'cats', 'sizes', 's_cat'));
    }

    public function categories()
    {
        $category_id = request('category_id');

        $cats = Category::whereNull('parent_id')->get();

        $q = Category::whereNotNull('parent_id');
        if (!empty($category_id)) {
            $q->where('parent_id', $category_id);
        }

        $subs = $q->get();

        return view('frontend.categories', compact('cats', 'subs'));
    }

    public function free_shipping()
    {
        $items = Product::with('variation')
            ->where('products.is_free_shipping', 1)
            ->select('products.*')
            ->orderByRaw('IF(priority IS NULL, 1, 0), priority ASC')
            ->latest()
            ->get();

        return view('frontend.products.free_shipping_products', compact('items'));
    }

    // Get The Price Of Variation Product
    public function get_variation_price(Request $request)
    {
        $data            = Product::find($request->product_id);
        $discount_amount = (int) $data->dicount_amount;
        $discount_type   = $data->discount_type;

        return response()->json([
            'success'         => true,
            'discount_amount' => $discount_amount,
            'discount_type'   => $discount_type
        ]);
    }
}
