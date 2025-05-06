<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Auth\ApiRegisterController;
use App\Http\Controllers\Seller\ProductsController as SellerProductsController;
use App\Http\Controllers\Admin\OrdersController as AdminOrdersController;
use App\Http\Controllers\Buyer\OrdersController as BuyerOrdersController;
use App\Http\Controllers\Seller\OrdersController as SellerOrdersController;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\SellerCollection;
use App\Http\Resources\SellerResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Redis;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/users', function () {
    return User::all()->toArray();
});

Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/register', [ApiRegisterController::class, 'store']);



Route::prefix('/admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/orders', [AdminOrdersController::class, 'index']);
    Route::delete('/orders/{order}', [AdminOrdersController::class, 'destroy']);
});

Route::prefix('seller.dashboard')->middleware(['auth:sanctum', 'role:seller'])->group(function () {

    Route::get('/logout', [ApiAuthController::class, 'logout']);

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

Route::get('/products', function (Request $request) {
    return Product::orderByDesc('created_at')->paginate()->toResourceCollection();
});
Route::get('/products/{id}', function (string $id) {
    return Product::find($id)->toResource();
});

Route::prefix('/seller')->group(function () {

    Route::get('/', function () {
        return new SellerCollection(User::isSeller()->get());
    });

    Route::get('/{id}', function (string $id) {
        return new SellerResource(
            User::isSeller()->findOrFail($id)
        );
    });

    Route::get('/{id}/products', function (string $id) {
        return new ProductCollection(User::isSeller()->findOrFail($id)->products()->orderByDesc('created_at')->paginate());
    });

    Route::get('/products/{id}', function ($id) {
        return Product::findOrFail($id)->user;
    });
});
