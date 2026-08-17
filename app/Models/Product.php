<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'barcode',
        'name',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock',
        'unit',
        'image',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function batches()
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function wholesalePrices()
    {
        return $this->hasMany(ProductWholesalePrice::class)->orderBy('min_qty', 'asc');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getWholesalePriceForQty($qty)
    {
        if (!$this->relationLoaded('wholesalePrices')) {
            $this->load('wholesalePrices');
        }

        $tier = $this->wholesalePrices
            ->where('min_qty', '<=', $qty)
            ->sortByDesc('min_qty')
            ->first();

        return $tier ? $tier->price : $this->selling_price;
    }

    public function isLowStock()
    {
        return $this->stock <= $this->min_stock;
    }
}
