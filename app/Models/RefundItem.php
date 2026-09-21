<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundItem extends Model
{
    use HasFactory;
    protected $table = 'refund_items';
    public $timestamps = false;
    protected $fillable = ['refund_id', 'order_id', 'order_item_id', 'product_id', 'quantity'];

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
