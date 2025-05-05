<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->sellerOrders;
    }

    public function show(Request $request, int $id)
    {
        $sellerOrders = $request->user()->sellerOrders();
        return $sellerOrders->findOrFail($id);
    }
}
