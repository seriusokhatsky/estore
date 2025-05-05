<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::take(100)->orderByDesc('created_at');
        if ($request->has('buyer_id')) {
            $buyerId = $request->buyer_id;
            $orders = User::findOrFail($buyerId)->buyerOrders();
        }
        if ($request->has('seller_id')) {
            $sellerId = $request->seller_id;
            $orders = User::isSeller()->findOrFail($sellerId)->sellerOrders();
        }
        if ($request->has('product_id')) {
            $orders->where('orders.product_id', '=', (int) $request->product_id);
        }
        return $orders->get()->toArray();
    }

    /**
    * Remove the specified resource from storage.
    */
    public function destroy(Order $order)
    {
        return $order->delete();
    }
}
