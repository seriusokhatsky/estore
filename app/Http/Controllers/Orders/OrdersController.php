<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
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
            'payment_status' => 'pending',
            'user_id' => Auth::id()
        ]);

        return response()->json(['message' => 'Order created with id - ' . $order->id], 200);
    }
}
