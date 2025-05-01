<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\UserScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Attributes\Scope;

class Product extends Model
{
    protected $fillable = ['name', 'description', 'price', 'file', 'user_id'];
    //

    #[Scope]
    protected function forCurrentSeller(Builder $query)
    {
        $query->where('user_id', Auth::id());
    }
}
