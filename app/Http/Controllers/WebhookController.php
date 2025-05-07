<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WebhookController extends Controller
{
    //
    public function handlePaymentWebhook(Request $request)
    {
        // validate signature.

        $request->validate([
            'payment_id' => [
                'required',
                'integer',
                'exists:App\Models\Payment,id'
            ],
            'status' => [
                'required',
                'string',
                'in:failed,paid',
                Rule::prohibitedIf(function () use ($request) {
                    $payment = Payment::find($request->payment_id);
                    return $payment && $payment->status === 'paid';
                })
            ],
        ]);

        $payment = Payment::find($request->payment_id);
        $payment->update(['status' => $request->status]);

        return response()->json(['message' => 'Payment status processed']);
    }
}
