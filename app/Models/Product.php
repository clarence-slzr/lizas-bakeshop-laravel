<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'is_available',
        'stock',
        'is_best_seller',
        'image',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_best_seller' => 'boolean',
        'price' => 'decimal:2',
    ];
}
