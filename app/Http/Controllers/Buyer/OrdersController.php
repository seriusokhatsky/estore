<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->buyerOrders()->get();
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:App\Models\Product,id',
        ]);

        $order = Order::create([
            'product_id' => $request->product_id,
            'status' => 'new',
            'user_id' => Auth::id()
        ]);

        return response()->json(['message' => 'Order created with id - ' . $order->id], 200);
    }
}
