<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\KidsWearDetail;
use App\Models\JewelleryDetail;

class CartController extends Controller
{



    public function index()
    {

    $data['cart'] = session()->get('cart', []);

    return view('pages.cart',$data);

    }





    /**
     * Add to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer',
            'variant_id' => 'required|integer',
            'qty'        => 'nullable|integer|min:1',
        ]);

        $qty = $request->qty ?? 1;

        $product = Product::with('type')->findOrFail($request->product_id);

        // Resolve variant based on product type
        $variant = match ($product->product_type) {
            'clothing' => KidsWearDetail::findOrFail($request->variant_id),
            'jewellery' => JewelleryDetail::findOrFail($request->variant_id),
            default => abort(404, 'Invalid product type'),
        };

        $cart = session()->get('cart', []);

        // Prevent ID collision between tables
        $key = $product->product_type . '_' . $variant->id;

        if (isset($cart[$key])) {
            $cart[$key]['qty'] += $qty;
        } else {
            $cart[$key] = [
                'key'   =>        $key,
                'product_id'   => $product->id,
                'variant_id'   => $variant->id,
                'type'         => $product->product_type,
                'name'         => $product->name,
                'variant_name' => $variant->name ?? '',
                'size'         => $variant->size?->name ?? '',
                'color'        => $variant->color?->name ?? '',
                'price'        => $variant->price,
                'qty'          => $qty,
                'image'        => $product->image,
            ];
        }

        session()->put('cart', $cart);

        return $this->miniCartHtml();
    }

    /**
     * Remove from cart
     */
    public function remove(Request $request)
    {
        $request->validate([
            'key' => 'required|string'
        ]);

        $cart = session()->get('cart', []);

        unset($cart[$request->key]);

        session()->put('cart', $cart);

        return $this->miniCartHtml();
    }

    /**
     * Update quantity
     */
    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'qty' => 'required|integer|min:1'
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->key])) {
            $cart[$request->key]['qty'] = $request->qty;
            session()->put('cart', $cart);
        }

        return $this->miniCartHtml();
    }

    /**
     * Mini cart HTML
     */
    protected function miniCartHtml()
    {
        $cart = session()->get('cart', []);
        $total = collect($cart)->sum(fn ($i) => $i['price'] * $i['qty']);

        return view('layouts.partials.mini_cart', compact('cart', 'total'))->render();
    }


    public function clear(Request $request)
    {
        // Remove only cart session
        session()->forget('cart');

        return $this->miniCartHtml();
    }


}
