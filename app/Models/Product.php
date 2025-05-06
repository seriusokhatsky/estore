<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'file', 'user_id'];
    //

    // Product is attached to sellers.
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
