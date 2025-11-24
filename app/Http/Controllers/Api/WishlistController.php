<?php
// app/Http/Controllers/Api/WishlistController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        Log::info('🔍 Wishlist fetch request started', [
            'user_id' => Auth::id(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);

        try {
            $user = Auth::user();
            
            Log::debug('👤 User authenticated for wishlist', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // ✅ YAHAN CHANGE KAREN: primary_image -> primaryImage
            $wishlistItems = Wishlist::with([
                'product.primaryImage', // camelCase use karen
                'product.variants'
            ])
            ->where('user_id', $user->id)
            ->get();

            Log::debug('📦 Wishlist items fetched', [
                'count' => $wishlistItems->count(),
                'item_ids' => $wishlistItems->pluck('id')->toArray()
            ]);

            $mappedItems = $wishlistItems->map(function ($item) {
                $mappedItem = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'slug' => $item->product->slug,
                        'base_price' => $item->product->base_price,
                        'sale_price' => $item->product->sale_price,
                        'in_stock' => $item->product->in_stock,
                        // ✅ YAHAN BHI CHANGE KAREN
                        'primary_image' => $item->product->primaryImage ? [ // primaryImage use karen
                            'image_path' => $item->product->primaryImage->image_path,
                            'alt_text' => $item->product->primaryImage->alt_text
                        ] : null,
                        'variants' => $item->product->variants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'color_name' => $variant->color_name,
                                'size_name' => $variant->size_name,
                                'additional_price' => $variant->additional_price,
                                'stock_quantity' => $variant->stock_quantity
                            ];
                        })
                    ]
                ];

                Log::debug('🔄 Wishlist item processed', [
                    'wishlist_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name
                ]);

                return $mappedItem;
            });

            Log::info('✅ Wishlist fetch completed successfully', [
                'user_id' => $user->id,
                'total_items' => $mappedItems->count(),
                'response_time' => microtime(true) - LARAVEL_START
            ]);

            return response()->json([
                'success' => true,
                'data' => $mappedItems
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Wishlist fetch failed', [
                'user_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wishlist'
            ], 500);
        }
    }

    public function add(Request $request)
    {
        Log::info('➕ Wishlist add request started', [
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString()
        ]);

        try {
            $user = Auth::user();
            $productId = $request->product_id;

            Log::debug('🔎 Checking existing wishlist item', [
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            $existing = Wishlist::where('user_id', $user->id)
                              ->where('product_id', $productId)
                              ->first();

            if ($existing) {
                Log::warning('⚠️ Product already in wishlist', [
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'existing_id' => $existing->id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Product already in wishlist'
                ], 400);
            }

            Log::debug('📝 Creating new wishlist item', [
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            $wishlistItem = Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            Log::info('✅ Product added to wishlist successfully', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'wishlist_id' => $wishlistItem->id,
                'timestamp' => $wishlistItem->created_at
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Wishlist add operation failed', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to wishlist'
            ], 500);
        }
    }

    public function remove(Request $request)
    {
        Log::info('➖ Wishlist remove request started', [
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString()
        ]);

        try {
            $user = Auth::user();
            $productId = $request->product_id;

            Log::debug('🔎 Finding wishlist item to remove', [
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            $deletedCount = Wishlist::where('user_id', $user->id)
                   ->where('product_id', $productId)
                   ->delete();

            Log::info('✅ Product removed from wishlist', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'items_deleted' => $deletedCount,
                'timestamp' => now()->toISOString()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Wishlist remove operation failed', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove product from wishlist'
            ], 500);
        }
    }

    public function check($productId)
    {
        Log::debug('🔍 Wishlist check request', [
            'user_id' => Auth::id(),
            'product_id' => $productId,
            'timestamp' => now()->toISOString()
        ]);

        try {
            $user = Auth::user();
            
            $inWishlist = Wishlist::where('user_id', $user->id)
                                ->where('product_id', $productId)
                                ->exists();

            Log::debug('✅ Wishlist check completed', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'in_wishlist' => $inWishlist
            ]);

            return response()->json([
                'success' => true,
                'in_wishlist' => $inWishlist
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Wishlist check failed', [
                'user_id' => Auth::id(),
                'product_id' => $productId,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check wishlist status'
            ], 500);
        }
    }
}