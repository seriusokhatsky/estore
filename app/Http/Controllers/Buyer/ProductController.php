<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return Product::orderByDesc('created_at')->paginate()->toResourceCollection();
    }

    public function show(Product $product)
    {
        return $product->toResource();
    }
}
