<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\CustomerAddress;

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;


class CheckoutController extends Controller
{
   

    public function index()
    {

        $data['cart'] = session('cart', []);
        $data['subtotal'] = collect($data['cart'])->sum(fn ($i) => $i['price'] * $i['qty']);
        $data['discount'] = session('discount', 0);
        $data['total'] = max(0, $data['subtotal'] - $data['discount']);

        $customer = auth('customer')->user();

        $data['addresses'] = $customer
        ? $customer->addresses()->latest()->get()
        : collect();

        return view('pages.checkout',$data);

    }




    public function confirm(Request $request)
    {
    $cart = session()->get('cart', []);

    if (empty($cart)) {
        return redirect()->route('home')
            ->with('error', 'Your cart is empty');
    }

    DB::beginTransaction();

    try {

        /* =====================
         | CUSTOMER (OPTIONAL)
         ===================== */
        $customer = auth('customer')->user();

       /* =====================
 | SHIPPING ADDRESS
 ===================== */
if (
    auth('customer')->check()
    && $request->filled('shipping_address_id')
    && $request->shipping_address_id !== 'new'
) {

    $address = CustomerAddress::where('id', $request->shipping_address_id)
        ->where('customer_id', auth('customer')->id())
        ->firstOrFail();

        $shipping = [
            'name'     => $address->fullname,
            'email'    => $address->email,
            'phone'    => $address->phone,
            'address'  => $address->address_line1,
            'city'     => $address->city,
            'state'    => $address->state,
            'pincode'  => $address->postal_code,
            'country'  => $address->country,
        ];

        } else {

            $shipping = $request->validate([
                'shipping.name'     => 'required|string',
                'shipping.phone'    => 'required|string',
                'shipping.email'    => 'required|email',
                'shipping.address'  => 'required|string',
                'shipping.city'     => 'required|string',
                'shipping.state'    => 'required|string',
                'shipping.pincode'  => 'required|string',
            ])['shipping'];
        }

        /* =====================
        | BILLING ADDRESS
        ===================== */
        if ($request->boolean('same_as_shipping')) {

            $billing = $shipping;

        } elseif (
            auth('customer')->check()
            && $request->filled('billing_address_id')
        ) {

        $address = CustomerAddress::where('id', $request->billing_address_id)
        ->where('customer_id', auth('customer')->id())
        ->firstOrFail();

        $billing = [
            'name'     => $address->fullname,
            'email'    => $address->email,
            'phone'    => $address->phone,
            'address'  => $address->address_line1,
            'city'     => $address->city,
            'state'    => $address->state,
            'pincode'  => $address->postal_code,
            'country'  => $address->country,
        ];

        } else {

            $billing = $request->validate([
                'billing.name'     => 'required|string',
                'billing.phone'    => 'required|string',
                'billing.email'    => 'required|email',
                'billing.address'  => 'required|string',
                'billing.city'     => 'required|string',
                'billing.state'    => 'required|string',
                'billing.pincode'  => 'required|string',
            ])['billing'];
        }




        /* =====================
         | TOTALS
         ===================== */
        $subtotal = collect($cart)->sum(fn ($i) => $i['price'] * $i['qty']);
        $discount = session('discount', 0);
        $tax = 0;
        $shippingCharge = 0;

        $total = $subtotal - $discount + $tax + $shippingCharge;

        /* =====================
         | CREATE ORDER
         ===================== */
        $orderId = DB::table('orders')->insertGetId([
            'order_number'      => 'ORD-' . strtoupper(Str::random(10)),

            'customer_id'       => $customer?->id,

            'subtotal'          => $subtotal,
            'discount_total'    => $discount,
            'tax_total'         => $tax,
            'shipping_total'    => $shippingCharge,
            'total'             => $total,

            'currency'          => 'EUR',

            'coupon_id'         => session('coupon.id'),
            'coupon_code'       => session('coupon.code'),
            'coupon_discount'   => $discount,

            'payment_method'    => 'online',
            'payment_status'    => 'pending',
            'status'            => 'pending',

            'billing_address'   => json_encode($billing),
            'shipping_address'  => json_encode($shipping),

            'customer_name'     => $shipping['name'],
            'customer_email'    => $shipping['email'],
            'customer_phone'    => $shipping['phone'],

            'placed_at'         => now(),
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        /* =====================
         | ORDER ITEMS
         ===================== */
        foreach ($cart as $item) {

            $lineSubtotal = $item['price'] * $item['qty'];

            DB::table('order_items')->insert([
                'order_id'   => $orderId,
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'title'      => $item['name'],
                'sku'        => $item['sku'] ?? null,
                'quantity'   => $item['qty'],
                'price'      => $item['price'],
                'subtotal'   => $lineSubtotal,
                'tax'        => 0,
                'total'      => $lineSubtotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::commit();



        Stripe::setApiKey('sk_test_51STgTm33KUb8rNOZU1zD2k8wEeKICUwGSEvGlvaUkrdqZ0ffWq9awVLM0uHK2tKqfVQXNmMgjHelt2Gu8P3EJ87H006xWzRI53');

        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items' => collect($cart)
                ->values()
                ->map(function ($item) {
                return [
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $item['name'],
                        ],
                        'unit_amount' => (int) ($item['price'] * 100),
                    ],
                    'quantity' => $item['qty'],
                ];
            })->toArray(),

            'mode' => 'payment',

            'customer_email' => $billing['email'],

            'metadata' => [
                'order_id' => $orderId,
            ],

            'success_url' => route('stripe.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.cancel', $orderId),
        ]);

        DB::table('orders')->where('id', $orderId)->update([
            'stripe_session_id' => $session->id,
        ]);

        DB::commit();


        return redirect($session->url);


        session()->forget(['cart', 'discount', 'coupon']);

        return redirect()->route('order.success', $orderId);

    } catch (\Throwable $e) {

        DB::rollBack();

        \Log::error('Order placement failed', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        ]);

        return back()->with('error', $e->getMessage());
    }
    }


    public function summary($orderId)
    {

        $order = Order::with([
                'items.product',
                'customer',
            ])
            ->where('id', $orderId)
            ->firstOrFail();

        return view('pages.order-summary', compact('order'));

    }


    public function stripeSuccess(Request $request)
    {
        \Stripe\Stripe::setApiKey('sk_test_51STgTm33KUb8rNOZU1zD2k8wEeKICUwGSEvGlvaUkrdqZ0ffWq9awVLM0uHK2tKqfVQXNmMgjHelt2Gu8P3EJ87H006xWzRI53');

        $session = \Stripe\Checkout\Session::retrieve($request->session_id);

        $orderId = $session->metadata->order_id;

        $order = Order::findOrFail($orderId);

        if ($order->payment_status !== 'paid') {
            $order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'status' => 'confirmed',
            ]);

            session()->forget(['cart', 'discount', 'coupon']);
        }

        return redirect()->route('order.success', $order->id);
    }



    public function stripeCancel(Order $order)
    {
        $order->update([
            'payment_status' => 'failed',
            'status' => 'cancelled',
        ]);

        return redirect()->route('checkout.view')
            ->with('error', 'Payment was cancelled.');
    }










}
