<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Category;

use App\Models\Product;

use App\Models\Testimonial;

class HomeController extends Controller
{
   

    public function index()
    {
        
    $data['new_arrivals'] = Product::latest()->take(6)->get();

    $data['featured'] = Product::latest()->where('featured', true)->take(6)->get();


    $data['budget_products'] = Product::where(function ($query) {
    $query->whereHas('kidsWearDetails', function ($q) {
        $q->where('price', '<', 2000);
    })->orWhereHas('jewelleryDetails', function ($q) {
        $q->where('price', '<', 2000);
    });
    })
    ->latest()   // order by products.created_at
    ->take(4)
    ->get();

    $data['testimonials'] = Testimonial::latest()->take(6)->get();

    return view('pages.index',$data);

    }


}

