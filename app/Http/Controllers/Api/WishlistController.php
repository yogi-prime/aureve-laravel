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
    public function add(Request $request)
    {
        try {
            $user = Auth::user();
            $productId = $request->product_id;

            $existing = Wishlist::where('user_id', $user->id)
                              ->where('product_id', $productId)
                              ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product already in wishlist'
                ], 400);
            }

            Wishlist::create([
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            Log::info('✅ Product added to wishlist', [
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Wishlist add failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to wishlist'
            ], 500);
        }
    }

    public function remove(Request $request)
    {
        try {
            $user = Auth::user();
            $productId = $request->product_id;

            Wishlist::where('user_id', $user->id)
                   ->where('product_id', $productId)
                   ->delete();

            Log::info('✅ Product removed from wishlist', [
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Wishlist remove failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove product from wishlist'
            ], 500);
        }
    }

    public function check($productId)
    {
        try {
            $user = Auth::user();
            
            $inWishlist = Wishlist::where('user_id', $user->id)
                                ->where('product_id', $productId)
                                ->exists();

            return response()->json([
                'success' => true,
                'in_wishlist' => $inWishlist
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check wishlist status'
            ], 500);
        }
    }
}