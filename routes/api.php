<?php

use App\Http\Controllers\Auth\ApiAuthController;
use App\Http\Controllers\Auth\ApiRegisterController;
use App\Http\Controllers\Products\ProductsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Product;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/api/login', [ApiAuthController::class, 'login']);

Route::prefix('api')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('/users', function() {
            return User::take(10)->orderByDesc('created_at')->get()->toArray();
        });

        Route::get('/logout', [ApiAuthController::class, 'logout']);

        // Route::get('/test', function (Request $request): array {
        //     $users = User::take(1)
        //         ->get()
        //         ->toArray();

        //     return $users;
        // });

        // Route::apiResource('products', ProductsController::class)->middleware('role:seller');

        // Route::post('/register', [ApiRegisterController::class, 'store']);
    });


Route::prefix('api')->group(function () {
    Route::prefix('seller')->middleware('auth:sanctum')->group(function () {
        Route::get('/users', function () {
            return User::take(10)->orderByDesc('created_at')->get()->toArray();
        });

        Route::get('/logout', [ApiAuthController::class, 'logout']);

        Route::apiResource('products', ProductsController::class)->middleware('role:seller');
    });

    Route::post('/register', [ApiRegisterController::class, 'store']);

    Route::get('/products', function (Request $request) {
        return Product::take(10)->orderByDesc('created_at')->get()->toArray();
    });
    Route::get('/products/{id}', function (string $id) {
        return Product::find($id);
    });
});
