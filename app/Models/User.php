<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @method bool isAdmin()
 * @method bool isCashier()
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'username',
        'full_name',
        'password',
        'role',
        'is_active',
        'last_login',
        'phone',
        'motto',
        'avatar',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'processed_by');
    }

    public function shifts()
    {
        return $this->hasMany(CashierShift::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class, 'processed_by');
    }
}