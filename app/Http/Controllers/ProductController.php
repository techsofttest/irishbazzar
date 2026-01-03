<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\ProductVarient;

use App\Models\Category;

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


        // Availability
        if ($request->in_stock) {
            $products->where('stock', '>', 0);
        }

        if ($request->out_stock) {
            $products->where('stock', '=', 0);
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
        $products->where('stock', '>', 0);
    }

    if ($request->out_stock) {
        $products->where('stock', '=', 0);
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

    if ($request->out_stock) {
        $products->whereHas('kidsWearDetails', function ($q) {
            $q->where('stock', '=', 0);
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
    'type'
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







}
