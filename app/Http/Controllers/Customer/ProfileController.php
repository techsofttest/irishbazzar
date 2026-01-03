<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;


class ProfileController extends Controller
{


    public function index()
    {
        $customer = auth('customer')->user();

        // Total orders
        $totalOrders = Order::where('customer_id', $customer->id)->count();

        // Total amount spent (only paid orders)
        $totalSpent = Order::where('customer_id', $customer->id)
                            ->where('payment_status', 'paid')
                            ->sum('total');

        // Total wishlist items
        $wishlistCount = Wishlist::where('customer_id', $customer->id)->count();

        return view('user.profile', compact(
            'totalOrders',
            'totalSpent',
            'wishlistCount'
        ));
    }





    public function orders()
    {
        $customer = auth('customer')->user();

        $orders = Order::with([
                'items.product'
            ])
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        return view('user.orders', compact('orders'));
    }



    public function orderDetails(Order $order)
    {
        // 🔒 Security: ensure order belongs to logged-in customer
        if ($order->customer_id !== auth('customer')->id()) {
            abort(403);
        }

        return view('user.order-details', [
            'order' => $order->load(['items.product']), // eager load relations
        ]);
    }




    /* Addess Management */

        public function addresses()
        {
            return view('user.addresses');
        }

        public function addressList()
        {
            return auth('customer')->user()->addresses()->orderByDesc('is_default')->get();
        }

        public function storeAddress(Request $request)
        {
            $customer = auth('customer')->user();

            if ($request->is_default) {
                $customer->addresses()->update(['is_default' => false]);
            }

            return CustomerAddress::create([
                'customer_id' => $customer->id,
                'label' => $request->addressType,
                'fullname' => $request->fullName,
                'address_line1' => $request->addressLine1,
                'address_line2' => $request->addressLine2,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->zipCode,
                'address_type' => strtolower($request->addressType),
                'is_default' => $request->isDefault ?? false,
            ]);
        }

        public function updateAddress(Request $request, $id)
        {
            $address = CustomerAddress::where('customer_id', auth('customer')->id())->findOrFail($id);

            if ($request->isDefault) {
                auth('customer')->user()->addresses()->update(['is_default' => false]);
            }

            $address->update([
                'label' => $request->addressType,
                'fullname' => $request->fullName,
                'address_line1' => $request->addressLine1,
                'address_line2' => $request->addressLine2,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'postal_code' => $request->zipCode,
                'address_type' => strtolower($request->addressType),
                'is_default' => $request->isDefault ?? false,
            ]);

            return response()->json(['success' => true]);
        }

        public function deleteAddress($id)
        {
            CustomerAddress::where('customer_id', auth('customer')->id())
                ->where('id', $id)
                ->delete();

            return response()->json(['success' => true]);
        }

        public function setDefault($id)
        {
            $customer = auth('customer')->user();

            $customer->addresses()->update(['is_default' => false]);
            $customer->addresses()->where('id', $id)->update(['is_default' => true]);

            return response()->json(['success' => true]);
        }






}
