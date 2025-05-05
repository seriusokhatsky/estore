<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Auth\ApiRegisterController;
use App\Http\Controllers\Orders\OrdersController;
use App\Http\Controllers\Products\ProductsController;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\SellerCollection;
use App\Http\Resources\SellerResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Product;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/api/login', [ApiAuthController::class, 'login']);

Route::prefix('api')->group(function () {
    Route::prefix('seller.dashboard')->middleware('auth:sanctum')->group(function () {

        Route::get('/logout', [ApiAuthController::class, 'logout']);

        Route::apiResource('products', ProductsController::class)->middleware('role:seller');
    });

    Route::post('/register', [ApiRegisterController::class, 'store']);

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

    Route::prefix('/orders')->group(function () {
        Route::get('/', function () {
            return Order::all()->toResourceCollection();
        });
        Route::post('/', [OrdersController::class, 'store']);

        Route::get('/{id}', function ($id) {
            return Order::findOrFail($id)->toResource();
        });
    });
});
