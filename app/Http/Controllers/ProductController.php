<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\ProductVarient;

use App\Models\Category;

use App\Models\ProductReview;
use App\Models\OrderItem;

use Illuminate\Support\Facades\DB;


class ProductController extends Controller
{
   
    public function index()
    {
        
    $data['pp_title'] = "All Products";

    $data['categories'] = Category::orderBy('name','asc')->get();

    $data['products'] = Product::where('is_active',1)->get();

    return view('pages.products',$data);

    }



    public function category(Request $request,Category $category)
    {
    
    $data['pp_title'] = $category->name;

    $data['categories'] = Category::orderBy('name','asc')->get();

    $products = Product::query()
        ->where('is_active', 1)
        ->where('category_id', $category->id);


        // DEFAULT → show only in-stock
    if (!$request->out_stock) {
        $products->where(function ($q) {
            $q->whereHas('kidsWearDetails', function ($q2) {
                $q2->where('stock', '>', 0);
            })->orWhereHas('jewelleryDetails', function ($q2) {
                $q2->where('stock', '>', 0);
            });
        });
    }

        // Price range (uses lowest_price accessor)
        if ($request->price_from) {
            $products->whereHas('jewelleryDetails', function ($q) use ($request) {
                $q->where('price', '>=', $request->price_from);
            });
        }

        if ($request->price_to) {
            $products->whereHas('jewelleryDetails', function ($q) use ($request) {
                $q->where('price', '<=', $request->price_to);
            });
        }

        // Categories
        if ($request->filled('categories')) {
            $products->whereIn('category_id', $request->categories);
        }

        $data['products'] = $products->get();


    return view('pages.products', $data);
    }




    public function collections()
    {

    $data['categories'] = Category::orderBy('name','asc')->get();

    return view('pages.collections',$data);

    }



   public function jewellery(Request $request)
{
    $data['pp_title'] = "Jewellery";

    $data['categories'] = Category::where('type', 'jewellery')->get();

    $products = Product::query()
        ->where('is_active', 1)
        ->where('product_type', 'jewellery');

    // Availability
        if ($request->in_stock) {
            $products->whereHas('jewelleryDetails', function ($q) {
                $q->where('stock', '>', 0);
            });
        }

        // DEFAULT → show only in-stock
    if (!$request->out_stock) {
        $products->whereHas('jewelleryDetails', function ($q) {
            $q->where('stock', '>', 0);
        });
    }

    // Price range (uses lowest_price accessor)
    if ($request->price_from) {
        $products->whereHas('jewelleryDetails', function ($q) use ($request) {
            $q->where('price', '>=', $request->price_from);
        });
    }

    if ($request->price_to) {
        $products->whereHas('jewelleryDetails', function ($q) use ($request) {
            $q->where('price', '<=', $request->price_to);
        });
    }

    // Categories
    if ($request->filled('categories')) {
        $products->whereIn('category_id', $request->categories);
    }

    $data['products'] = $products->get();

    return view('pages.products', $data);
    }



    public function clothing(Request $request)
    {
    $data['pp_title'] = "Kids Wear";

    $data['categories'] = Category::where('type', 'clothing')->get();

    $products = Product::query()
        ->where('is_active', 1)
        ->where('product_type', 'clothing');

    /* =====================
     | Availability Filter
     ===================== */

    if ($request->in_stock) {
        $products->whereHas('kidsWearDetails', function ($q) {
            $q->where('stock', '>', 0);
        });
    }

    // DEFAULT → show only in-stock
    if (!$request->out_stock) {
        $products->whereHas('kidsWearDetails', function ($q) {
            $q->where('stock', '>', 0);
        });
    }
    /* =====================
     | Price Filter
     ===================== */

    if ($request->price_from) {
        $products->whereHas('kidsWearDetails', function ($q) use ($request) {
            $q->where('price', '>=', $request->price_from);
        });
    }

    if ($request->price_to) {
        $products->whereHas('kidsWearDetails', function ($q) use ($request) {
            $q->where('price', '<=', $request->price_to);
        });
    }

    /* =====================
     | Category Filter
     ===================== */

    if ($request->filled('categories')) {
        $products->whereIn('category_id', $request->categories);
    }

    $data['products'] = $products->get();

    return view('pages.products', $data);
    }




    public function detail($slug)
    {
        
    $data['product'] = Product::with([
    'kidsWearDetails.size',
    'kidsWearDetails.color',
    'jewelleryDetails.size',
    'type',
    'approvedReviews.customer'
    ])
    ->where('slug', $slug)
    ->where('is_active', 1)
    ->firstOrFail();

    // Dynamically get variant collection
    $data['variants'] = $data['product']->details()?->get() ?? collect();

    // Sizes (only for clothing)
    $data['sizes'] = $data['variants']->pluck('size')->filter()->unique('id')->values();

    // Colors
    $data['colors'] = $data['variants']->pluck('color')->filter()->unique('id')->values();



    // ⭐ Review stats
    $data['reviewStats'] = [
        'avg'   => $data['product']->average_rating ?? 0,
        'count' => $data['product']->reviews_count ?? 0,
        'stars' => $data['product']->approvedReviews()
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->toArray(),
    ];

    // ✔ Can customer review?
    $data['canReview'] = auth('customer')->check()
        && \App\Models\ProductReview::customerHasPurchasedProduct(
            auth('customer')->id(),
            $data['product']->id
        )
        && ! $data['product']->reviews()
            ->where('customer_id', auth('customer')->id())
            ->exists();




    $data['related'] = Product::where('is_active', 1)
    ->where('id', '!=', $data['product']->id)
    ->where('category_id', $data['product']->category_id)
    ->limit(6)
    ->get();

    return view('pages.product-detail',$data);

    }


    public function fetch_jewellery_variant(request $request)
    {

    $size = $request->input('size');

    $variants = DB::table('jewellery_details')
                    ->where('size_id', $size)
                    ->first();

    // Return JSON response
    return response()->json($variants);
    
    }


    public function fetch_clothing_variant(request $request)
    {

    $size = $request->input('size');

    $color = $request->input('color');

    $variants = DB::table('kids_wear_details')
                    ->where('size_id', $size)
                    ->where('color_id', $color)
                    ->first();

    // Return JSON response
    return response()->json($variants);
    
    }


    public function clothingVariantsMap($productId)
    {
        $variants = DB::table('kids_wear_details')
            ->where('product_id', $productId)
            ->where('stock', '>', 0)
            ->select('id', 'color_id', 'size_id', 'price', 'stock')
            ->get();

        return response()->json($variants);
    }





    public function liveSearch(Request $request)
    {
        $keyword = $request->get('q');

        if (!$keyword) {
            return response()->json([]);
        }

        $products = Product::where('is_active', 1)
            ->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%");
            })
            ->select('id', 'name', 'slug', 'image')
            ->limit(8)
            ->get();

        return response()->json($products);
    }





    public function storeReview(Request $request, Product $product)
{
    $customer = auth('customer')->user();
    abort_if(!$customer, 403);

    // ✔ Validate
    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'review' => 'required|string|min:10',
    ]);

    // ✔ Must have purchased
    abort_if(
        !ProductReview::customerHasPurchasedProduct($customer->id, $product->id),
        403,
        'You must purchase this product before reviewing.'
    );

    // ✔ One review per customer
    abort_if(
        ProductReview::where('product_id', $product->id)
            ->where('customer_id', $customer->id)
            ->exists(),
        403,
        'You already reviewed this product.'
    );

    // ✔ Get order ID (first paid order containing this product)
    $orderId = OrderItem::where('product_id', $product->id)
        ->whereHas('order', function ($q) use ($customer) {
            $q->where('customer_id', $customer->id)
              ->where('payment_status', 'paid');
        })
        ->value('order_id');

    ProductReview::create([
        'product_id'  => $product->id,
        'customer_id' => $customer->id,
        'order_id'    => $orderId,
        'rating'      => $request->rating,
        'review'      => $request->review,
    ]);

    return back()->with('success', 'Review submitted for approval.');
}





}
