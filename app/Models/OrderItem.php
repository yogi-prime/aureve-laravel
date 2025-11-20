<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'product_id', 'variant_id', 'product_name',
        'variant_attributes', 'price', 'quantity', 'total'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Order relationship
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Product relationship
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Variant relationship
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}