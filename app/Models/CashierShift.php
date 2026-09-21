<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    use HasFactory;
    protected $table = 'cashier_shifts';
    public $timestamps = false;
    protected $fillable = ['user_id', 'shift_start', 'shift_end', 'starting_cash', 'ending_cash', 'total_sales', 'status', 'order_count', 'notes'];
    protected $casts = ['shift_start' => 'datetime', 'shift_end' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
