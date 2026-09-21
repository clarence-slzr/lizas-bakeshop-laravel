<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;
    protected $table = 'refunds';
    public $timestamps = false;
    protected $fillable = ['order_id', 'refund_amount', 'reason', 'refund_items', 'processed_by', 'refund_date', 'status', 'notes'];
    protected $casts = ['refund_date' => 'datetime'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
    public function items()
    {
        return $this->hasMany(RefundItem::class);
    }
}
