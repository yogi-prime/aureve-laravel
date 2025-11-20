<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'image', 'parent_id', 
        'sort_order', 'is_active', 'meta_title', 'meta_description', 'meta_keywords'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Parent category relationship
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Child categories relationship
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Products relationship
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Active products only
    public function activeProducts(): HasMany
    {
        return $this->hasMany(Product::class)->where('is_active', true);
    }
}