<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'balance', 'held_balance', 'total_earned', 'total_spent', 'total_withdrawn'];

    protected $casts = [
        'balance' => 'integer',
        'held_balance' => 'integer',
        'total_earned' => 'integer',
        'total_spent' => 'integer',
        'total_withdrawn' => 'integer',
    ];

    protected $appends = ['available_balance'];

    /**
     * Available balance = total balance minus held/escrowed funds.
     */
    public function getAvailableBalanceAttribute(): int
    {
        return max(0, (int) $this->balance - (int) $this->held_balance);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class, 'user_id', 'user_id');
    }
}
