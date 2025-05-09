<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/dev')->group(function () {
    Route::get('/products', function () {
        if (!app()->environment(['local', 'development'])) {
            abort(404);
        }
        return Product::with('user')->orderByDesc('created_at')->take(100)->get()->toResourceCollection();
    });

    Route::get('/sellers', function () {
        if (!app()->environment(['local', 'development'])) {
            abort(404);
        }
        return User::isSeller()->take(100)->get();
    });

    Route::get('/orders', function () {
        if (!app()->environment(['local', 'development'])) {
            abort(404);
        }
        return Order::take(10)->get();
    });

    Route::get('/user', function (Request $request) {
        if (!app()->environment(['local', 'development'])) {
            abort(404);
        }
        return $request->user();
    });

    Route::get('/users', function () {
        if (!app()->environment(['local', 'development'])) {
            abort(404);
        }
        return User::all()->toArray();
    });
});
