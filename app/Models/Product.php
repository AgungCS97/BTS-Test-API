<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'price', 'description', 'category', 'images',
        'created_by', 'created_by_id', 'updated_by', 'updated_by_id'
    ];

    protected $casts = [
        'images' => 'array', // json otomatis jadi aray
    ];
}
