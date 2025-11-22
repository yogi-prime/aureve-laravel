<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        try {
            Log::channel('category')->info('Fetching all active parent categories', [
                'method' => 'index',
                'timestamp' => now()->toISOString()
            ]);

            $categories = Category::with(['children' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('sort_order', 'asc');
            }])
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('sort_order', 'asc')
            ->get();

            // Add product counts to categories
            $categories->each(function($category) {
                $category->product_count = $this->getCategoryProductCount($category);
                $category->children->each(function($child) {
                    $child->product_count = $this->getCategoryProductCount($child);
                });
            });

            Log::channel('category')->info('Categories fetched successfully', [
                'method' => 'index',
                'categories_count' => $categories->count(),
                'categories_with_counts' => $categories->map(function($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'product_count' => $category->product_count,
                        'children_count' => $category->children->count(),
                        'children' => $category->children->map(function($child) {
                            return [
                                'id' => $child->id,
                                'name' => $child->name,
                                'slug' => $child->slug,
                                'product_count' => $child->product_count
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            Log::channel('category')->error('Failed to fetch categories', [
                'method' => 'index',
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch categories'
            ], 500);
        }
    }

    public function showBySlug($slug)
    {
        try {
            Log::channel('category')->info('Fetching category by slug', [
                'method' => 'showBySlug',
                'slug' => $slug
            ]);

            // Get category with active children and their product counts
            $category = Category::with(['children' => function($query) {
                $query->where('is_active', true)
                      ->orderBy('sort_order', 'asc')
                      ->withCount(['activeProducts']);
            }])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

            if (!$category) {
                Log::channel('category')->warning('Category not found by slug', [
                    'method' => 'showBySlug',
                    'slug' => $slug
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Category not found',
                ], 404);
            }

            // Get all subcategory IDs including the current category
            $categoryIds = $this->getAllSubcategoryIds($category);
            
            // Calculate total product count for this category and all subcategories
            $totalProductCount = Product::whereIn('category_id', $categoryIds)
                ->where('is_active', true)
                ->count();

            // Add product counts to children
            $category->children->each(function($child) {
                $child->product_count = $child->active_products_count;
            });

            // Get category stats
            $stats = $this->getCategoryStats($category, $totalProductCount);

            Log::channel('category')->info('Category found successfully', [
                'method' => 'showBySlug',
                'slug' => $slug,
                'category_id' => $category->id,
                'category_name' => $category->name,
                'children_count' => $category->children->count(),
                'total_product_count' => $totalProductCount,
                'stats' => $stats
            ]);

            // Fetch products for this category and all subcategories
            $products = Product::whereIn('category_id', $categoryIds)
                ->where('is_active', true)
                ->with(['images' => function ($q) {
                    $q->where('is_primary', true);
                }])
                ->with(['category' => function($q) {
                    $q->select('id', 'name', 'slug');
                }])
                ->orderBy('created_at', 'desc')
                ->get();

            $response = [
                'success' => true,
                'data' => [
                    'category' => $category,
                    'products' => $products,
                    'stats' => $stats,
                    'total_products' => $totalProductCount,
                    'subcategories' => $category->children->map(function($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'slug' => $child->slug,
                            'product_count' => $child->product_count,
                            'description' => $child->description
                        ];
                    })
                ]
            ];

            Log::channel('category')->info('Sending category with products response', [
                'method' => 'showBySlug',
                'slug' => $slug,
                'products_count' => $products->count(),
                'subcategories_count' => $category->children->count()
            ]);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::channel('category')->error('Error in showBySlug', [
                'method' => 'showBySlug',
                'slug' => $slug,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch category'
            ], 500);
        }
    }

    /**
     * Get all subcategory IDs including the category itself
     */
    private function getAllSubcategoryIds($category)
    {
        $ids = [$category->id];
        
        // Recursively get all child category IDs
        $getChildIds = function($category) use (&$getChildIds, &$ids) {
            foreach ($category->children as $child) {
                $ids[] = $child->id;
                if ($child->children->count() > 0) {
                    $getChildIds($child);
                }
            }
        };
        
        $getChildIds($category);
        return $ids;
    }

    /**
     * Get product count for a category including all subcategories
     */
    private function getCategoryProductCount($category)
    {
        $categoryIds = $this->getAllSubcategoryIds($category);
        return Product::whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Generate dynamic stats for category
     */
    private function getCategoryStats($category, $productCount)
    {
        $baseStats = [
            [
                'label' => 'Curated Designs', 
                'value' => $productCount > 0 ? "{$productCount}+" : '50+'
            ],
            [
                'label' => 'Handcrafted', 
                'value' => '100%'
            ],
            [
                'label' => 'Ships in', 
                'value' => '3–5 days'
            ]
        ];

        // Add premium stats for luxury categories
        if (stripos($category->name, 'gold') !== false || 
            stripos($category->name, 'diamond') !== false ||
            stripos($category->name, 'premium') !== false) {
            $baseStats[] = [
                'label' => 'Certified', 
                'value' => 'Hallmarked'
            ];
        }

        return $baseStats;
    }

    /**
     * Get products with advanced filtering
     */
    public function productsBySlug($slug, Request $request)
{
    try {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Get all subcategory IDs
        $categoryIds = $this->getAllSubcategoryIds($category);

        // Build query with filters
        $query = Product::whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->with(['images' => function ($q) {
                $q->where('is_primary', true);
            }])
            ->with(['category' => function($q) {
                $q->select('id', 'name', 'slug');
            }]);

        // Apply sorting - FIXED: Use base_price instead of price
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price-low':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price-high':
                $query->orderBy('base_price', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'featured':
                $query->orderBy('is_featured', 'desc')->orderBy('created_at', 'desc');
                break;
            default: // newest
                $query->orderBy('created_at', 'desc');
                break;
        }

        // Apply price filter - FIXED: Use base_price instead of price
        if ($request->has('min_price')) {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        $products = $query->get();

        // Transform products data - FIXED: Use base_price and sale_price
        $transformedProducts = $products->map(function($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => $product->base_price, // Use base_price as current price
                'original_price' => $product->sale_price ? $product->base_price : null, // If there's a sale, base_price becomes original
                'sale_price' => $product->sale_price, // Add sale_price if needed
                'description' => $product->description,
                'is_featured' => $product->is_featured,
                'is_bestseller' => $product->is_bestseller,
                'is_new' => $product->created_at->gt(now()->subDays(30)),
                'category' => $product->category ? [
                    'name' => $product->category->name,
                    'slug' => $product->category->slug
                ] : null,
                'images' => $product->images->map(function($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->url,
                        'is_primary' => $image->is_primary
                    ];
                }),
                'badge' => $this->getProductBadge($product)
            ];
        });

        return response()->json([
            'success' => true,
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ],
            'filters' => $request->all(),
            'total_products' => $products->count(),
            'data' => $transformedProducts,
        ]);

    } catch (\Exception $e) {
        Log::channel('category')->error('Error in productsBySlug', [
            'method' => 'productsBySlug',
            'slug' => $slug,
            'error_message' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch products'
        ], 500);
    }
}

    /**
     * Determine product badge based on properties
     */
    private function getProductBadge($product)
    {
        if ($product->is_bestseller) return 'Bestseller';
        if ($product->created_at->gt(now()->subDays(30))) return 'New';
        if ($product->is_featured) return 'Featured';
        return null;
    }
}