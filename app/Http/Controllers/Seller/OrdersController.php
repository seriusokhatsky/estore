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
        $sellerOrders = $request->user()->sellerOrders();
        if ($request->has('buyer_id')) {
            $sellerOrders->where('orders.user_id', '=', (int) $request->buyer_id);
        }
        if ($request->has('product_id')) {
            $sellerOrders->where('orders.product_id', '=', (int) $request->product_id);
        }
        return $sellerOrders->get();
    }

    public function show(Request $request, int $id)
    {
        $sellerOrders = $request->user()->sellerOrders();
        return $sellerOrders->findOrFail($id);
    }
}
