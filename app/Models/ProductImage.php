<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'variant_id', 'image_path', 'alt_text', 'is_primary', 'sort_order'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

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

    // Scope for primary images
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    // Scope ordered by sort order
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}