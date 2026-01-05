<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'image',
        'parent_id',
        'sort_order',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Parent collection relationship (for nested collections)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Collection::class, 'parent_id');
    }

    /**
     * Child collections relationship (for nested collections)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Collection::class, 'parent_id');
    }

    /**
     * Products relationship (many-to-many)
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_product')
                    ->withTimestamps();
    }

    /**
     * Active products only
     */
    public function activeProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'collection_product')
                    ->where('is_active', true)
                    ->withTimestamps();
    }

    /**
     * Get active children
     */
    public function activeChildren(): HasMany
    {
        return $this->hasMany(Collection::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order', 'asc');
    }
}
