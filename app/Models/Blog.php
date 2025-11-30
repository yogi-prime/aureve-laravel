<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Blog extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'featured_image_alt',
        'category_id',
        'is_published',
        'is_featured',
        'published_at',
        'author_name',
        'author_image',
        'author_bio',
        'reading_time',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image',
        'og_type',
        'twitter_card',
        'twitter_title',
        'twitter_description',
        'twitter_image',
        'schema_markup',
        'robots_index',
        'robots_follow',
        'view_count',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'robots_index' => 'boolean',
        'robots_follow' => 'boolean',
        'published_at' => 'datetime',
        'schema_markup' => 'array',
        'view_count' => 'integer',
        'reading_time' => 'integer',
    ];

    // Auto-generate slug from title
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
            
            // Auto-calculate reading time
            if ($blog->content) {
                $wordCount = str_word_count(strip_tags($blog->content));
                $blog->reading_time = max(1, ceil($wordCount / 200));
            }
        });

        static::updating(function ($blog) {
            // Recalculate reading time on update
            if ($blog->isDirty('content') && $blog->content) {
                $wordCount = str_word_count(strip_tags($blog->content));
                $blog->reading_time = max(1, ceil($wordCount / 200));
            }
        });
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_blog_tag');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    // SEO Helpers - Auto generate from content
    public function generateSeoFromContent(): array
    {
        $seoTitle = $this->seo_title ?: $this->title;
        
        // Generate description from excerpt or content
        $seoDescription = $this->seo_description;
        if (empty($seoDescription)) {
            $seoDescription = $this->excerpt ?: Str::limit(strip_tags($this->content), 160);
        }
        
        // Generate keywords from category and tags
        $keywords = [];
        if ($this->category) {
            $keywords[] = $this->category->name;
        }
        foreach ($this->tags as $tag) {
            $keywords[] = $tag->name;
        }
        $seoKeywords = $this->seo_keywords ?: implode(', ', $keywords);

        return [
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'seo_keywords' => $seoKeywords,
        ];
    }

    // Generate full SEO data for frontend
    public function getFullSeoData(): array
    {
        $generated = $this->generateSeoFromContent();
        
        return [
            // Basic SEO
            'title' => $generated['seo_title'],
            'description' => $generated['seo_description'],
            'keywords' => $generated['seo_keywords'],
            'canonical' => $this->canonical_url,
            
            // Open Graph
            'og' => [
                'title' => $this->og_title ?: $generated['seo_title'],
                'description' => $this->og_description ?: $generated['seo_description'],
                'image' => $this->og_image ?: $this->featured_image,
                'type' => $this->og_type,
                'url' => $this->canonical_url,
            ],
            
            // Twitter Card
            'twitter' => [
                'card' => $this->twitter_card,
                'title' => $this->twitter_title ?: $generated['seo_title'],
                'description' => $this->twitter_description ?: $generated['seo_description'],
                'image' => $this->twitter_image ?: $this->og_image ?: $this->featured_image,
            ],
            
            // Robots
            'robots' => $this->getRobotsDirective(),
            
            // Schema/JSON-LD
            'schema' => $this->getSchemaMarkup(),
        ];
    }

    public function getRobotsDirective(): string
    {
        $directives = [];
        $directives[] = $this->robots_index ? 'index' : 'noindex';
        $directives[] = $this->robots_follow ? 'follow' : 'nofollow';
        return implode(', ', $directives);
    }

    public function getSchemaMarkup(): array
    {
        if ($this->schema_markup) {
            return $this->schema_markup;
        }

        // Auto-generate Article schema
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $this->title,
            'description' => $this->excerpt ?: Str::limit(strip_tags($this->content), 160),
            'image' => $this->featured_image,
            'author' => [
                '@type' => 'Person',
                'name' => $this->author_name ?: 'Aureve',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Aureve',
            ],
            'datePublished' => $this->published_at?->toIso8601String(),
            'dateModified' => $this->updated_at->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->canonical_url,
            ],
        ];
    }

    // Increment view count
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
