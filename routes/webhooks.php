<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('/webhooks')->group(function () {
    Route::patch('/payment', [WebhookController::class, 'handlePaymentWebhook']);
});
