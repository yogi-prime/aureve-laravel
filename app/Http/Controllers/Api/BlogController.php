<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BlogController extends Controller
{
    /**
     * Get all published blogs with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 12);
        $category = $request->get('category');
        $tag = $request->get('tag');
        $featured = $request->get('featured');
        $search = $request->get('search');

        $query = Blog::with(['category', 'tags'])
            ->published()
            ->latest('published_at');

        if ($category) {
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        if ($tag) {
            $query->whereHas('tags', function ($q) use ($tag) {
                $q->where('slug', $tag);
            });
        }

        if ($featured === 'true' || $featured === '1') {
            $query->featured();
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $blogs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $blogs->items(),
            'meta' => [
                'current_page' => $blogs->currentPage(),
                'last_page' => $blogs->lastPage(),
                'per_page' => $blogs->perPage(),
                'total' => $blogs->total(),
            ],
        ]);
    }

    /**
     * Get a single blog by slug with full SEO data
     */
    public function show(string $slug): JsonResponse
    {
        $blog = Blog::with(['category', 'tags'])
            ->where('slug', $slug)
            ->published()
            ->first();

        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found',
            ], 404);
        }

        // Increment view count
        $blog->incrementViewCount();

        // Get full SEO data
        $seoData = $blog->getFullSeoData();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'slug' => $blog->slug,
                'excerpt' => $blog->excerpt,
                'content' => $blog->content,
                'featured_image' => $blog->featured_image ? asset('storage/' . $blog->featured_image) : null,
                'featured_image_alt' => $blog->featured_image_alt,
                'category' => $blog->category ? [
                    'id' => $blog->category->id,
                    'name' => $blog->category->name,
                    'slug' => $blog->category->slug,
                ] : null,
                'tags' => $blog->tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                        'slug' => $tag->slug,
                    ];
                }),
                'author' => [
                    'name' => $blog->author_name,
                    'image' => $blog->author_image ? asset('storage/' . $blog->author_image) : null,
                    'bio' => $blog->author_bio,
                ],
                'reading_time' => $blog->reading_time,
                'view_count' => $blog->view_count,
                'published_at' => $blog->published_at?->toIso8601String(),
                'updated_at' => $blog->updated_at->toIso8601String(),
                'seo' => $seoData,
            ],
        ]);
    }

    /**
     * Get featured blogs
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 6);

        $blogs = Blog::with(['category'])
            ->published()
            ->featured()
            ->latest('published_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blogs->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'slug' => $blog->slug,
                    'excerpt' => $blog->excerpt,
                    'featured_image' => $blog->featured_image ? asset('storage/' . $blog->featured_image) : null,
                    'featured_image_alt' => $blog->featured_image_alt,
                    'category' => $blog->category ? [
                        'name' => $blog->category->name,
                        'slug' => $blog->category->slug,
                    ] : null,
                    'reading_time' => $blog->reading_time,
                    'published_at' => $blog->published_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Get related blogs based on category and tags
     */
    public function related(string $slug, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 4);

        $blog = Blog::where('slug', $slug)->first();

        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog not found',
            ], 404);
        }

        $tagIds = $blog->tags->pluck('id')->toArray();

        $related = Blog::with(['category'])
            ->published()
            ->where('id', '!=', $blog->id)
            ->where(function ($query) use ($blog, $tagIds) {
                $query->where('category_id', $blog->category_id)
                      ->orWhereHas('tags', function ($q) use ($tagIds) {
                          $q->whereIn('blog_tags.id', $tagIds);
                      });
            })
            ->latest('published_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $related->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'slug' => $blog->slug,
                    'excerpt' => $blog->excerpt,
                    'featured_image' => $blog->featured_image ? asset('storage/' . $blog->featured_image) : null,
                    'category' => $blog->category ? [
                        'name' => $blog->category->name,
                        'slug' => $blog->category->slug,
                    ] : null,
                    'reading_time' => $blog->reading_time,
                    'published_at' => $blog->published_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Get all blog categories
     */
    public function categories(): JsonResponse
    {
        $categories = BlogCategory::active()
            ->withCount(['blogs' => function ($query) {
                $query->published();
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'blogs_count' => $category->blogs_count,
                    'seo_title' => $category->seo_title,
                    'seo_description' => $category->seo_description,
                ];
            }),
        ]);
    }

    /**
     * Get all blog tags
     */
    public function tags(): JsonResponse
    {
        $tags = BlogTag::active()
            ->withCount(['blogs' => function ($query) {
                $query->published();
            }])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'blogs_count' => $tag->blogs_count,
                ];
            }),
        ]);
    }

    /**
     * Get blog archive (grouped by month/year)
     */
    public function archive(): JsonResponse
    {
        $archive = Blog::published()
            ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $archive,
        ]);
    }

    /**
     * Get blog sitemap data for SEO
     */
    public function sitemap(): JsonResponse
    {
        $blogs = Blog::published()
            ->select(['slug', 'updated_at', 'published_at'])
            ->orderByDesc('published_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $blogs->map(function ($blog) {
                return [
                    'slug' => $blog->slug,
                    'lastmod' => $blog->updated_at->toIso8601String(),
                    'published_at' => $blog->published_at?->toIso8601String(),
                ];
            }),
        ]);
    }
}
