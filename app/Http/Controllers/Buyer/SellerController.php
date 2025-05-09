<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Http\Resources\SellerCollection;
use App\Http\Resources\ProductCollection;

class SellerController extends Controller
{
    public function index()
    {
        return new SellerCollection(User::isSeller()->take(20)->get());
    }

    public function show(int $id)
    {
        return User::isSeller()->findOrFail($id);
    }

    public function products(int $id)
    {
        return new ProductCollection(User::isSeller()->findOrFail($id)->products()->orderByDesc('created_at')->paginate());
    }

    public function showProduct(Product $product)
    {
        return $product->toResource();
    }
}
