<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'type',
        'description',
        'cashier_name',
        'customer_name',
        'supplier_name',
        'total_amount',
        'debt_amount',
        'due_date',
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

    public function scopePenjualan($query)
    {
        return $query->where('type', 'penjualan');
    }

    public function scopePengeluaran($query)
    {
        return $query->where('type', 'pengeluaran');
    }

    public function scopePembelian($query)
    {
        return $query->where('type', 'pembelian');
    }

    public function scopeBayarHutang($query)
    {
        return $query->where('type', 'bayar_hutang');
    }

    public function getTypeLabelAttribute()
    {
        return match ($this->type) {
            'penjualan' => 'Penjualan',
            'pengeluaran' => 'Pengeluaran Operasional',
            'pembelian' => 'Pembelian Stok',
            'bayar_hutang' => 'Bayar Hutang',
            default => 'Penjualan',
        };
    }
}
