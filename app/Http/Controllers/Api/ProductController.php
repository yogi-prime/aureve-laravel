<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductSeo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::with(['primaryImage', 'variants', 'category'])
                ->where('is_active', true);

            // Search filter
            if ($request->has('q') && $request->q) {
                $search = $request->q;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('short_description', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('category', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            // Category filter
            if ($request->has('category') && $request->category !== 'All') {
                if (is_numeric($request->category)) {
                    $query->where('category_id', $request->category);
                } else {
                    $query->whereHas('category', function($q) use ($request) {
                        $q->where('slug', $request->category);
                    });
                }
            }

            // Price filter
            if ($request->has('min_price')) {
                $query->where('base_price', '>=', $request->min_price);
            }
            if ($request->has('max_price')) {
                $query->where('base_price', '<=', $request->max_price);
            }

            // Availability filter
            if ($request->has('only_available') && $request->only_available) {
                $query->where('in_stock', true);
            }

            // Featured filter
            if ($request->has('featured') && $request->featured) {
                $query->where('is_featured', true);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'featured');
            switch ($sortBy) {
                case 'price-asc':
                    $query->orderBy('base_price', 'asc');
                    break;
                case 'price-desc':
                    $query->orderBy('base_price', 'desc');
                    break;
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                default:
                    $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
                    break;
            }

            $products = $query->paginate($request->get('per_page', 12));

            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function show($id)
    {
        try {
            $product = Product::with([
                'category',
                'variants' => function($query) {
                    $query->where('is_active', true);
                },
                'images' => function($query) {
                    $query->orderBy('is_primary', 'desc')
                          ->orderBy('sort_order', 'asc');
                },
                'seo',
                'sizeGuide'
            ])->where('is_active', true)->findOrFail($id);

            // Increment view count
            $product->increment('view_count');

            Log::info('✅ Product fetched successfully', ['product_id' => $id]);

            return response()->json([
                'success' => true,
                'data' => $product
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Product fetch failed', [
                'product_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function showBySlug($slug)
    {
        try {
            $product = Product::with([
                'category',
                'variants' => function($query) {
                    $query->where('is_active', true);
                },
                'images' => function($query) {
                    $query->orderBy('is_primary', 'desc')
                          ->orderBy('sort_order', 'asc');
                },
                'seo',
                'sizeGuide'
            ])->where('slug', $slug)
              ->where('is_active', true)
              ->firstOrFail();

            // Increment view count
            $product->increment('view_count');

            return response()->json([
                'success' => true,
                'data' => $product
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    public function similarProducts($productId, Request $request)
    {
        try {
            $product = Product::findOrFail($productId);
            $categoryId = $request->category_id;

            $similarProducts = Product::with(['primaryImage'])
                ->where('category_id', $categoryId)
                ->where('id', '!=', $productId)
                ->where('is_active', true)
                ->where('in_stock', true)
                ->inRandomOrder()
                ->limit(8)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $similarProducts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch similar products'
            ], 500);
        }
    }
}