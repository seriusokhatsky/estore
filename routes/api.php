<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Auth\ApiRegisterController;
use App\Http\Controllers\Seller\ProductsController as SellerProductsController;
use App\Http\Controllers\Admin\OrdersController as AdminOrdersController;
use App\Http\Controllers\Buyer\OrdersController as BuyerOrdersController;
use App\Http\Controllers\Buyer\PaymentController as BuyerPaymentController;
use App\Http\Controllers\Buyer\ProductController as BuyerProductController;
use App\Http\Controllers\Seller\OrdersController as SellerOrdersController;
use App\Http\Controllers\Buyer\SellerController as BuyerSellerCollection;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\SellerCollection;
use App\Http\Resources\SellerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Product;

Route::middleware('guest')->group(function () {
    Route::post('/login', [ApiAuthController::class, 'login']);
    Route::post('/register', [ApiRegisterController::class, 'store']);
});

Route::get('/logout', [ApiAuthController::class, 'logout'])->middleware('auth:sanctum');

Route::prefix('/admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/orders', [AdminOrdersController::class, 'index']);
    Route::delete('/orders/{order}', [AdminOrdersController::class, 'destroy']);
    Route::patch('/orders/{order}/status', [AdminOrdersController::class, 'updateStatus']);
});

Route::prefix('/seller.dashboard')->middleware(['auth:sanctum', 'role:seller'])->group(function () {

    Route::apiResource('products', SellerProductsController::class);

    Route::prefix('/orders')->group(function () {
        Route::get('/', [SellerOrdersController::class, 'index']);
        Route::get('/{id}', [SellerOrdersController::class, 'show']);
    });
});

Route::prefix('/orders')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [BuyerOrdersController::class, 'index']);
    Route::post('/', [BuyerOrdersController::class, 'store']);
});

Route::prefix('/payments')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/', [BuyerPaymentController::class, 'store']);
});

Route::get('/products', [BuyerProductController::class, 'index']);
Route::get('/products/{product}', [BuyerProductController::class, 'show']);

Route::prefix('/seller')->group(function () {
    Route::get('/', [BuyerSellerCollection::class, 'index']);
    Route::get('/{id}', [BuyerSellerCollection::class, 'show']);
    Route::get('/{id}/products', [BuyerSellerCollection::class, 'products']);
    Route::get('/products/{product}', [BuyerSellerCollection::class, 'showProduct']);
});

require __DIR__ . '/local.php';
require __DIR__ . '/webhooks.php';
