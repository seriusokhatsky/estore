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
        $orders = Order::take(20)->orderByDesc('created_at');

        return $orders
            ->when($request->has('buyer_id'), function ($query) use ($request) {
                return User::findOrFail($request->buyer_id)->buyerOrders();
            })
            ->when($request->has('seller_id'), function ($query) use ($request) {
                return User::isSeller()->findOrFail($request->seller_id)->sellerOrders();
            })
            ->when($request->has('product_id'), function ($query) use ($request) {
                return $query->where('orders.product_id', '=', (int) $request->product_id);
            })
            ->get()
            ->toResourceCollection();
    }

    /**
    * Remove the specified resource from storage.
    */
    public function destroy(Order $order)
    {
        return $order->delete();
    }

    /**
     * Update the status of the specified order.
     *
     * @param  \App\Models\Order  $order
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,processing,completed,cancelled'
        ]);

        $order->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order
        ]);
    }
}
