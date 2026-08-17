<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'type',
        'cash_category_id',
        'amount',
        'payment_method',
        'account_name',
        'reference_number',
        'notes',
        'user_id',
    ];

    public function category()
    {
        return $this->belongsTo(CashCategory::class, 'cash_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
