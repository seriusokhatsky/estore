<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public $fillable = ['product_id', 'user_id', 'status', 'payment_status'];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): HasOne
    {
        return $this->hasOne(Product::class);
    }
}
