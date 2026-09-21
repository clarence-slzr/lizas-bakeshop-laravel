<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders';
    public $timestamps = false;
    protected $fillable = ['customer_name', 'contact_number', 'total_amount', 'subtotal', 'order_type', 'delivery_address', 'landmark', 'status', 'order_date', 'processed_by', 'discount', 'discount_type'];
    protected $casts = ['order_date' => 'datetime'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
}
