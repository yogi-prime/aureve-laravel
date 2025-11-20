<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSeo extends Model
{
    use HasFactory;
protected $table = 'product_seo';
    protected $fillable = [
        'product_id', 'meta_title', 'meta_description', 'meta_keywords',
        'og_title', 'og_description', 'og_image', 'canonical_url',
        'structured_data', 'focus_keyword', 'seo_content'
    ];

    protected $casts = [
        'structured_data' => 'array',
    ];

    // Product relationship
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}