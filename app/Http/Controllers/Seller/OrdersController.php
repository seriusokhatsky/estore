<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()
            ->sellerOrders()
            ->when($request->has('buyer_id'), function ($query) use ($request) {
                return $query->where('orders.user_id', '=', (int) $request->buyer_id);
            })
            ->when($request->has('product_id'), function ($query) use ($request) {
                return $query->where('orders.product_id', '=', (int) $request->product_id);
            })
            ->get();
    }

    public function show(Request $request, int $id)
    {
        return $request->user()
            ->sellerOrders()
            ->findOrFail($id);
    }
}
