<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'price', 'file', 'user_id'];
    //

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
