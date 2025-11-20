<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function index(Request $request)
    {
        Log::info('Cart index called', ['user_id' => Auth::id()]);
        
        try {
            $cartItems = Cart::with(['product.primaryImage', 'variant'])
                ->where('user_id', Auth::id())
                ->get();

            Log::debug('Cart items fetched', ['count' => $cartItems->count()]);

            $cartTotal = $cartItems->sum(function ($item) {
                return $item->price * $item->quantity;
            });

            $cartCount = $cartItems->sum('quantity');

            Log::info('Cart totals calculated', [
                'cart_total' => $cartTotal,
                'cart_count' => $cartCount
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $cartItems,
                    'cart_total' => $cartTotal,
                    'cart_count' => $cartCount
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Cart index failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cart items'
            ], 500);
        }
    }

    public function add(Request $request)
    {
        Log::info('Add to cart request', [
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'variant_id' => $request->variant_id,
            'quantity' => $request->quantity
        ]);

        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|exists:products,id',
                'variant_id' => 'nullable|exists:product_variants,id',
                'quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                Log::warning('Cart add validation failed', [
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $product = Product::findOrFail($request->product_id);
            
            // Determine price
            if ($request->variant_id) {
                $variant = ProductVariant::findOrFail($request->variant_id);
                $price = $variant->sale_price ?? $variant->price;
                Log::debug('Variant price selected', ['variant_id' => $variant->id, 'price' => $price]);
            } else {
                $price = $product->sale_price ?? $product->base_price;
                Log::debug('Product price selected', ['product_id' => $product->id, 'price' => $price]);
            }

            // Check if item already exists in cart
            $existingCartItem = Cart::where('user_id', Auth::id())
                ->where('product_id', $request->product_id)
                ->where('variant_id', $request->variant_id)
                ->first();

            if ($existingCartItem) {
                // Update quantity if item exists
                $existingCartItem->update([
                    'quantity' => $existingCartItem->quantity + $request->quantity
                ]);

                $cartItem = $existingCartItem;
                
                Log::info('Cart item quantity updated', [
                    'cart_id' => $existingCartItem->id,
                    'new_quantity' => $existingCartItem->quantity
                ]);
            } else {
                // Create new cart item
                $cartItem = Cart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $request->product_id,
                    'variant_id' => $request->variant_id,
                    'quantity' => $request->quantity,
                    'price' => $price,
                ]);
                
                Log::info('New cart item created', [
                    'cart_id' => $cartItem->id,
                    'product_id' => $cartItem->product_id
                ]);
            }

            $cartItem->load(['product.primaryImage', 'variant']);

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully',
                'data' => $cartItem
            ]);

        } catch (\Exception $e) {
            Log::error('Cart add failed', [
                'user_id' => Auth::id(),
                'product_id' => $request->product_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Cart $cart)
    {
        Log::info('Cart update request', [
            'cart_id' => $cart->id,
            'user_id' => Auth::id(),
            'quantity' => $request->quantity
        ]);

        try {
            if ($cart->user_id !== Auth::id()) {
                Log::warning('Unauthorized cart update attempt', [
                    'cart_user_id' => $cart->user_id,
                    'current_user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                Log::warning('Cart update validation failed', [
                    'cart_id' => $cart->id,
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cart->update([
                'quantity' => $request->quantity
            ]);

            $cart->load(['product.primaryImage', 'variant']);

            Log::info('Cart item updated successfully', [
                'cart_id' => $cart->id,
                'new_quantity' => $cart->quantity
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'data' => $cart
            ]);

        } catch (\Exception $e) {
            Log::error('Cart update failed', [
                'cart_id' => $cart->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart'
            ], 500);
        }
    }

    public function remove(Cart $cart)
    {
        Log::info('Remove cart item request', [
            'cart_id' => $cart->id,
            'user_id' => Auth::id()
        ]);

        try {
            if ($cart->user_id !== Auth::id()) {
                Log::warning('Unauthorized cart removal attempt', [
                    'cart_user_id' => $cart->user_id,
                    'current_user_id' => Auth::id()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action'
                ], 403);
            }

            $cartId = $cart->id;
            $cart->delete();

            Log::info('Cart item removed successfully', ['cart_id' => $cartId]);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Cart removal failed', [
                'cart_id' => $cart->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item from cart'
            ], 500);
        }
    }

    public function clear()
    {
        Log::info('Clear cart request', ['user_id' => Auth::id()]);

        try {
            $deletedCount = Cart::where('user_id', Auth::id())->delete();
            
            Log::info('Cart cleared successfully', [
                'user_id' => Auth::id(),
                'items_removed' => $deletedCount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Cart clear failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cart'
            ], 500);
        }
    }
}