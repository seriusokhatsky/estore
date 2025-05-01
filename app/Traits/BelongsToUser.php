<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait BelongsToUser
{
    protected static function belongsToUser()
    {
        static::creating(function (Model $model) {
            if (Auth::check()) {
                $model->user_id = Auth::id();
            }
        });
    }
}
