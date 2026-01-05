<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CollectionController extends Controller
{
    /**
     * Get all active parent collections with their children
     */
    public function index(): JsonResponse
    {
        $collections = Collection::with(['children' => function($query) {
            $query->where('is_active', true)
                  ->orderBy('sort_order', 'asc');
        }])
        ->where('is_active', true)
        ->whereNull('parent_id')
        ->orderBy('sort_order', 'asc')
        ->get();

        // Add product count to each collection
        $collections->transform(function ($collection) {
            $collection->product_count = $collection->products()->count();

            // Add product count to children
            $collection->children->transform(function ($child) {
                $child->product_count = $child->products()->count();
                return $child;
            });

            return $collection;
        });

        return response()->json([
            'success' => true,
            'data' => $collections
        ]);
    }

    /**
     * Get collection by ID
     */
    public function show($id): JsonResponse
    {
        $collection = Collection::with(['children' => function($query) {
            $query->where('is_active', true)
                  ->orderBy('sort_order', 'asc');
        }])
        ->where('is_active', true)
        ->find($id);

        if (!$collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);
        }

        $collection->product_count = $collection->products()->count();

        return response()->json([
            'success' => true,
            'data' => $collection
        ]);
    }

    /**
     * Get collection by slug with products
     */
    public function showBySlug($slug): JsonResponse
    {
        $collection = Collection::with(['children' => function($query) {
            $query->where('is_active', true)
                  ->orderBy('sort_order', 'asc');
        }])
        ->where('is_active', true)
        ->where('slug', $slug)
        ->first();

        if (!$collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);
        }

        // Get all collection IDs (current + children)
        $collectionIds = collect([$collection->id]);
        $collection->children->each(function ($child) use (&$collectionIds) {
            $collectionIds->push($child->id);
        });

        // Count products in this collection and children
        $productCount = Product::whereHas('collections', function($query) use ($collectionIds) {
            $query->whereIn('collections.id', $collectionIds);
        })->where('is_active', true)->count();

        $collection->product_count = $productCount;

        // Add product count to children
        $collection->children->transform(function ($child) {
            $child->product_count = $child->products()->where('is_active', true)->count();
            return $child;
        });

        return response()->json([
            'success' => true,
            'data' => $collection
        ]);
    }

    /**
     * Get products by collection slug with filtering
     */
    public function productsBySlug($slug, Request $request): JsonResponse
    {
        $collection = Collection::with(['children' => function($query) {
            $query->where('is_active', true);
        }])
        ->where('is_active', true)
        ->where('slug', $slug)
        ->first();

        if (!$collection) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);
        }

        // Get all collection IDs (current + children)
        $collectionIds = collect([$collection->id]);
        $collection->children->each(function ($child) use (&$collectionIds) {
            $collectionIds->push($child->id);
        });

        // Build query
        $query = Product::with(['primaryImage', 'category', 'variants'])
            ->whereHas('collections', function($q) use ($collectionIds) {
                $q->whereIn('collections.id', $collectionIds);
            })
            ->where('is_active', true);

        // Apply sorting
        $sort = $request->get('sort', 'featured');
        switch ($sort) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'price-low':
                $query->orderByRaw('COALESCE(sale_price, base_price) ASC');
                break;
            case 'price-high':
                $query->orderByRaw('COALESCE(sale_price, base_price) DESC');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'featured':
            default:
                $query->orderBy('is_featured', 'desc')
                      ->orderBy('sort_order', 'asc');
                break;
        }

        // Apply price filters
        if ($request->has('min_price')) {
            $query->where(function($q) use ($request) {
                $q->where('sale_price', '>=', $request->min_price)
                  ->orWhere(function($q2) use ($request) {
                      $q2->whereNull('sale_price')
                         ->where('base_price', '>=', $request->min_price);
                  });
            });
        }

        if ($request->has('max_price')) {
            $query->where(function($q) use ($request) {
                $q->where('sale_price', '<=', $request->max_price)
                  ->orWhere(function($q2) use ($request) {
                      $q2->whereNull('sale_price')
                         ->where('base_price', '<=', $request->max_price);
                  });
            });
        }

        $perPage = $request->get('per_page', 12);
        $products = $query->paginate($perPage);

        // Transform products
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'short_description' => $product->short_description,
                'base_price' => $product->base_price,
                'sale_price' => $product->sale_price,
                'final_price' => $product->final_price,
                'is_on_sale' => $product->is_on_sale,
                'discount_percentage' => $product->discount_percentage,
                'in_stock' => $product->in_stock,
                'is_featured' => $product->is_featured,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'slug' => $product->category->slug,
                ] : null,
                'primary_image' => $product->primaryImage ? [
                    'image_path' => $product->primaryImage->image_path,
                    'alt_text' => $product->primaryImage->alt_text,
                ] : null,
                'variants' => $product->variants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'color_name' => $variant->color_name,
                        'size' => $variant->size,
                        'price' => $variant->price,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'collection' => [
                'id' => $collection->id,
                'name' => $collection->name,
                'slug' => $collection->slug,
                'description' => $collection->description,
                'image' => $collection->image,
            ],
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ]
        ]);
    }

    /**
     * Get all collections with product counts
     */
    public function indexWithCounts(): JsonResponse
    {
        $collections = Collection::with(['children' => function($query) {
            $query->where('is_active', true)
                  ->orderBy('sort_order', 'asc');
        }])
        ->where('is_active', true)
        ->whereNull('parent_id')
        ->orderBy('sort_order', 'asc')
        ->get();

        $collections->transform(function ($collection) {
            // Get all IDs including children
            $allIds = collect([$collection->id]);
            $collection->children->each(function ($child) use (&$allIds) {
                $allIds->push($child->id);
            });

            $collection->total_product_count = Product::whereHas('collections', function($q) use ($allIds) {
                $q->whereIn('collections.id', $allIds);
            })->where('is_active', true)->count();

            $collection->product_count = $collection->products()->where('is_active', true)->count();

            // Add counts to children
            $collection->children->transform(function ($child) {
                $child->product_count = $child->products()->where('is_active', true)->count();
                return $child;
            });

            return $collection;
        });

        return response()->json([
            'success' => true,
            'data' => $collections
        ]);
    }
}
