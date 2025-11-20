<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    /**
     * Display user's wishlist
     */
    public function index()
    {
        $wishlistItems = Auth::user()->wishlists()->with(['product.primaryImage', 'product.variants'])->get();

        return view('wishlist.index', compact('wishlistItems'));
    }

    /**
     * Add product to wishlist
     */
    public function add(Request $request)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please login to add items to wishlist',
                'redirect' => route('login')
            ], 401);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        try {
            // Check if product is already in wishlist
            $existingWishlist = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->first();

            if ($existingWishlist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product is already in your wishlist'
                ], 400);
            }

            // Add to wishlist
            Wishlist::create([
                'user_id' => Auth::id(),
                'product_id' => $request->product_id
            ]);

            $wishlistCount = Auth::user()->wishlists()->count();

            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist successfully!',
                'wishlist_count' => $wishlistCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding product to wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove product from wishlist
     */
    public function remove(Wishlist $wishlist)
    {
        // Check if user owns this wishlist item
        if ($wishlist->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        try {
            $wishlist->delete();

            $wishlistCount = Auth::user()->wishlists()->count();

            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist successfully!',
                'wishlist_count' => $wishlistCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing product from wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove product from wishlist by product ID
     */
    public function removeByProduct($productId)
    {
        try {
            $wishlist = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->firstOrFail();

            $wishlist->delete();

            $wishlistCount = Auth::user()->wishlists()->count();

            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist successfully!',
                'wishlist_count' => $wishlistCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing product from wishlist: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if product is in wishlist
     */
    public function check($productId)
    {
        $inWishlist = Auth::check() ? Auth::user()->hasInWishlist($productId) : false;

        return response()->json([
            'in_wishlist' => $inWishlist
        ]);
    }

    /**
     * Get wishlist count (for AJAX updates)
     */
    public function getCount()
    {
        $wishlistCount = Auth::check() ? Auth::user()->wishlists()->count() : 0;
        
        return response()->json([
            'wishlist_count' => $wishlistCount
        ]);
    }
}