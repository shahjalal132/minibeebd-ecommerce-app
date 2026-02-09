<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HoneyLandingPage;
use App\Models\Product;
use Illuminate\Support\Facades\Log;

class HoneyLandingPageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = HoneyLandingPage::orderBy('created_at', 'desc')->get();
        return view('backend.honey_landing_pages.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::where('status', 1)->get();
        return view('backend.honey_landing_pages.create', compact('products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'boolean'
        ]);

        // Initialize empty content structure
        $content = [
            'hero' => ['heading' => '', 'questions' => []],
            'welcome' => ['heading' => '', 'message' => '', 'logo' => ''],
            'description' => '',
            'why_buy' => ['center_image' => '', 'cards' => []],
            'why_eat_honey' => ['cards' => []],
            'reviews' => ['type' => 'review', 'screenshot' => '', 'review_cards' => []],
            'faq' => ['items' => []],
            'product' => [
                'type' => 'static',
                'product_id' => null,
                'title' => '',
                'image' => '',
                'quantity' => '',
                'regular_price' => null,
                'offer_price' => null,
                'short_description' => ''
            ]
        ];

        $data['content'] = $content;
        $data['status'] = $request->has('status') ? 1 : 0;

        $page = HoneyLandingPage::create($data);

        return response()->json([
            'status' => true,
            'msg' => 'Honey Landing Page created successfully!',
            'url' => route('admin.honey_landing_pages.edit', $page->id)
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $page = HoneyLandingPage::findOrFail($id);
        $products = Product::where('status', 1)->get();
        return view('backend.honey_landing_pages.edit', compact('page', 'products'));
    }

    /**
     * Update a specific section of the landing page
     */
    public function updateSection(Request $request, $id)
    {
        $page = HoneyLandingPage::findOrFail($id);
        $section = $request->input('section');

        // Get existing content or initialize
        $content = $page->content ?? [];

        // Handle different sections
        switch ($section) {
            case 'hero':
                $content['hero'] = [
                    'heading' => $request->input('content.hero.heading', ''),
                    'questions' => $request->input('content.hero.questions', [])
                ];
                break;

            case 'welcome':
                $welcomeData = [
                    'heading' => $request->input('content.welcome.heading', ''),
                    'message' => $request->input('content.welcome.message', ''),
                    'logo' => $content['welcome']['logo'] ?? ''
                ];
                
                if ($request->hasFile('logo')) {
                    $welcomeData['logo'] = $this->handleFileUpload($request->file('logo'), 'honey_landing');
                }
                
                $content['welcome'] = $welcomeData;
                break;

            case 'description':
                $content['description'] = $request->input('content.description', '');
                break;

            case 'why_buy':
                $whyBuyData = [
                    'center_image' => $content['why_buy']['center_image'] ?? '',
                    'cards' => []
                ];
                
                if ($request->hasFile('center_image')) {
                    $whyBuyData['center_image'] = $this->handleFileUpload($request->file('center_image'), 'honey_landing');
                }
                
                // Handle card icons and data
                $cardsInput = $request->input('content.why_buy.cards', []);
                if (is_array($cardsInput)) {
                    foreach ($cardsInput as $index => $card) {
                        $cardData = [
                            'heading' => $card['heading'] ?? '',
                            'description' => $card['description'] ?? '',
                            'icon' => $card['icon'] ?? ''
                        ];
                        
                        $iconKey = "card_icon_{$index}";
                        if ($request->hasFile($iconKey)) {
                            $cardData['icon'] = $this->handleFileUpload($request->file($iconKey), 'honey_landing');
                        }
                        
                        $whyBuyData['cards'][] = $cardData;
                    }
                }
                
                $content['why_buy'] = $whyBuyData;
                break;

            case 'why_eat_honey':
                $whyEatData = ['cards' => []];
                
                // Handle card icons and data
                $cardsInput = $request->input('content.why_eat_honey.cards', []);
                if (is_array($cardsInput)) {
                    foreach ($cardsInput as $index => $card) {
                        $cardData = [
                            'title' => $card['title'] ?? '',
                            'description' => $card['description'] ?? '',
                            'icon' => $card['icon'] ?? ''
                        ];
                        
                        $iconKey = "eat_card_icon_{$index}";
                        if ($request->hasFile($iconKey)) {
                            $cardData['icon'] = $this->handleFileUpload($request->file($iconKey), 'honey_landing');
                        }
                        
                        $whyEatData['cards'][] = $cardData;
                    }
                }
                
                $content['why_eat_honey'] = $whyEatData;
                break;

            case 'reviews':
                $reviewsData = [
                    'type' => $request->input('content.reviews.type', 'review'),
                    'screenshot' => $content['reviews']['screenshot'] ?? '',
                    'review_cards' => []
                ];
                
                if ($request->hasFile('screenshot')) {
                    $reviewsData['screenshot'] = $this->handleFileUpload($request->file('screenshot'), 'honey_landing');
                }
                
                // Handle review avatars and data
                $reviewCardsInput = $request->input('content.reviews.review_cards', []);
                if (is_array($reviewCardsInput)) {
                    foreach ($reviewCardsInput as $index => $card) {
                        $cardData = [
                            'name' => $card['name'] ?? '',
                            'rating' => $card['rating'] ?? 5,
                            'details' => $card['details'] ?? '',
                            'avatar' => $card['avatar'] ?? ''
                        ];
                        
                        $avatarKey = "review_avatar_{$index}";
                        if ($request->hasFile($avatarKey)) {
                            $cardData['avatar'] = $this->handleFileUpload($request->file($avatarKey), 'honey_landing');
                        }
                        
                        $reviewsData['review_cards'][] = $cardData;
                    }
                }
                
                $content['reviews'] = $reviewsData;
                break;

            case 'faq':
                $content['faq'] = [
                    'items' => $request->input('content.faq.items', [])
                ];
                break;

            case 'product':
                $productType = $request->input('content.product.type', 'static');
                $productId = $request->input('content.product.product_id');
                
                $productData = [
                    'type' => $productType,
                    'product_id' => $productId,
                    'title' => $request->input('content.product.title', ''),
                    'image' => $content['product']['image'] ?? '',
                    'quantity' => $request->input('content.product.quantity', ''),
                    'regular_price' => $request->input('content.product.regular_price'),
                    'offer_price' => $request->input('content.product.offer_price'),
                    'short_description' => $request->input('content.product.short_description', '')
                ];
                
                // If existing product is selected, fetch and populate product data
                if ($productType === 'existing' && !empty($productId)) {
                    $product = Product::where('id', $productId)->where('status', 1)->first();

                    Log::info('honey_landing_page_product: '.json_encode($product));
                    
                    if ($product) {
                        // Get product image path (save as path, not full URL for consistency)
                        $productImage = '';
                        if (!empty($product->image)) {
                            // Save as path relative to public directory (e.g., 'products/image.jpg')
                            $productImage = 'products/' . $product->image;
                        }
                        
                        // Determine prices: regular_price = purchase_prices, offer_price = sell_price
                        // Note: The column name is 'purchase_prices' (plural) in the database
                        $regularPrice = !empty($product->purchase_prices) ? (float)$product->purchase_prices : 0;
                        $offerPrice = !empty($product->sell_price) ? (float)$product->sell_price : 0;
                        
                        // Populate product data from existing product
                        $productData['title'] = $product->name;
                        $productData['image'] = $productImage;
                        $productData['regular_price'] = $regularPrice;
                        $productData['offer_price'] = $offerPrice;
                        $productData['quantity'] = ''; // Clear quantity for existing products
                        $productData['short_description'] = ''; // Clear description for existing products
                    }
                }
                
                // Handle manual image upload (for static products or to override existing product image)
                if ($request->hasFile('product_image')) {
                    $productData['image'] = $this->handleFileUpload($request->file('product_image'), 'honey_landing');
                }
                
                $content['product'] = $productData;
                break;
        }

        $page->content = $content;
        $page->save();

        return response()->json([
            'status' => true,
            'msg' => ucfirst($section) . ' section updated successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $page = HoneyLandingPage::findOrFail($id);
        $page->delete();

        return response()->json([
            'status' => true,
            'msg' => 'Honey Landing Page deleted successfully!'
        ]);
    }

    /**
     * Handle file upload
     */
    private function handleFileUpload($file, $directory = 'honey_landing')
    {
        $originName = $file->getClientOriginalName();
        $fileName = pathinfo($originName, PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $fileName = $fileName . time() . '.' . $extension;
        
        $uploadPath = public_path($directory);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $file->move($uploadPath, $fileName);
        return $directory . '/' . $fileName;
    }
}
