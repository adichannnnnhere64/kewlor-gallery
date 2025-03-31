<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = [
        'ip_address',
        'is_like',
    ];

    public function likeable()
    {
        return $this->morphTo();
    }
}
