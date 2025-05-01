<?php

namespace App\Http\Controllers\Products;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Product::take(10)
            ->forCurrentSeller()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|decimal:0,2',
            'file' => 'required|string|max:255'
        ]);

        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'file' => $request->file,
            'user_id' => Auth::id()
        ]);

        return response()->json(['message' => 'Product created with id - ' . $product->id], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return Product::forCurrentSeller()->findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $product = Product::forCurrentSeller()->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|decimal:0,2',
            'file' => 'required|string|max:255'
        ]);

        $product->update($data);

        return response()->json(['message' => 'Updated succesfully.'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        return $product->delete();
    }
}
