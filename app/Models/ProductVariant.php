<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'color_name', 'color_code', 'size', 'material', 'price', 'sale_price',
        'stock_quantity', 'sku', 'weight', 'dimensions', 'variant_description', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
        'dimensions' => 'array',
    ];

    // Product relationship
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Images relationship
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    // Carts relationship
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    // Order items relationship
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Get final price
    public function getFinalPriceAttribute()
    {
        return $this->sale_price ?? $this->price;
    }

    // Get variant attributes as string
    public function getVariantAttributesAttribute()
    {
        $attributes = [];
        if ($this->color_name) $attributes[] = "Color: {$this->color_name}";
        if ($this->size) $attributes[] = "Size: {$this->size}";
        if ($this->material) $attributes[] = "Material: {$this->material}";
        
        return implode(', ', $attributes);
    }
}