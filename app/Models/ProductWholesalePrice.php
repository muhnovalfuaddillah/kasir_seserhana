<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductWholesalePrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'min_qty',
        'unit_label',
        'price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
