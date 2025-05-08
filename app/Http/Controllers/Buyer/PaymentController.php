<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id' => [
                'required',
                'integer',
                'exists:App\Models\Order,id',
                Rule::unique('payments', 'order_id')->where(function ($query) {
                    return $query->where('status', '!=', 'failed');
                })
            ],
            // 'payment_method' => 'required|string|in:credit_card,paypal,bank_transfer'
        ]);

        $order = $request->user()->buyerOrders()->findOrFail($request->order_id);

        $orderProduct = $order->product;

        $payment = Payment::create([
            'order_id' => $request->order_id,
            'status' => 'pending',
            'amount' => $orderProduct->price,
            'payment_method' => 'credit-card'
        ]);

        return response()->json(['message' => 'Payment created with id - ' . $payment->id], 200);
    }
}
