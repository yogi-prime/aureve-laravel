<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'short_description', 'description', 'base_price', 'sale_price',
        'sku', 'stock_quantity', 'in_stock', 'is_featured', 'is_active', 'brand', 'model',
        'specifications', 'category_id', 'view_count', 'sort_order'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'in_stock' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'specifications' => 'array',
    ];

    // Category relationship
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // Variants relationship
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Active variants only
    public function activeVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    // Images relationship
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    // Primary image
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    // SEO relationship
    public function seo(): HasOne
    {
        return $this->hasOne(ProductSeo::class);
    }

    // Tags relationship
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    // Carts relationship
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    // Wishlists relationship
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    // Order items relationship
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Scope for active products
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for featured products
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // Scope for in-stock products
    public function scopeInStock($query)
    {
        return $query->where('in_stock', true);
    }

    // Get final price (sale price if available, else base price)
    public function getFinalPriceAttribute()
    {
        return $this->sale_price ?? $this->base_price;
    }

    // Check if product is on sale
    public function getIsOnSaleAttribute()
    {
        return !is_null($this->sale_price) && $this->sale_price < $this->base_price;
    }

    // Get discount percentage
    public function getDiscountPercentageAttribute()
    {
        if (!$this->is_on_sale) return 0;
        
        return round((($this->base_price - $this->sale_price) / $this->base_price) * 100);
    }
}