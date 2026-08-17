<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'phone',
        'address',
        'credit_limit',
        'current_debt',
        'type',
        'status',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getAvailableCreditAttribute()
    {
        if ($this->credit_limit <= 0) {
            return null; // Unlimited
        }
        return max(0, $this->credit_limit - $this->current_debt);
    }
}
