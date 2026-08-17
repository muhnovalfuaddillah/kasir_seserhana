<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    use HasFactory;

    protected $fillable = [
        'shift_number',
        'user_id',
        'branch_id',
        'start_time',
        'end_time',
        'starting_cash',
        'expected_cash',
        'actual_cash',
        'cash_difference',
        'total_sales_cash',
        'total_sales_qris',
        'total_sales_edc',
        'total_transactions',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public static function activeShift($userId = null)
    {
        $userId = $userId ?: auth()->id();
        return self::where('user_id', $userId)
            ->where('status', 'open')
            ->latest()
            ->first();
    }
}
