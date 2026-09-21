<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    public $timestamps = false;
    protected $fillable = ['name', 'price', 'category', 'is_available', 'stock'];
    protected $casts = ['is_available' => 'boolean'];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
