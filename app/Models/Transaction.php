<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'cashier_name',
        'customer_name',
        'total_amount',
        'discount_amount',
        'pay_amount',
        'change_amount',
        'payment_method',
        'status',
    ];

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }
}
