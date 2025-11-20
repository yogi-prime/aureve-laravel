<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id', 'variant_id', 'quantity', 'price', 'options'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'options' => 'array',
    ];

    // User relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

    // Calculate total for this cart item
    public function getTotalAttribute()
    {
        return $this->price * $this->quantity;
    }
}