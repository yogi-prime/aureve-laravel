<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * Display user's cart
     */
    public function index()
    {
        $cartItems = Auth::user()->carts()->with(['product.primaryImage', 'variant'])->get();
        $cartTotal = Auth::user()->cart_total;
        $cartCount = Auth::user()->cart_items_count;

        return view('cart.index', compact('cartItems', 'cartTotal', 'cartCount'));
    }

    /**
     * Add item to cart
     */
  public function add(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'variant_id' => 'nullable|exists:product_variants,id',
                'quantity' => 'required|integer|min:1'
            ]);

            $product = Product::findOrFail($request->product_id);
            
            // Check if variant exists and belongs to product
            if ($request->variant_id) {
                $variant = ProductVariant::where('id', $request->variant_id)
                    ->where('product_id', $product->id)
                    ->firstOrFail();
                
                // Check variant stock
                if ($variant->stock_quantity < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only ' . $variant->stock_quantity . ' items available in stock'
                    ]);
                }
            } else {
                // Check product stock
                if ($product->stock_quantity < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only ' . $product->stock_quantity . ' items available in stock'
                    ]);
                }
            }

            // If user is logged in, use database cart
            if (Auth::check()) {
                return $this->addToDatabaseCart($request, $product);
            } else {
                // Use session cart for guests
                return $this->addToSessionCart($request, $product);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while adding to cart: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Update cart item quantity
     */
    public function update(Request $request, Cart $cart)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        // Check if user owns this cart item
        if ($cart->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        try {
            // Check stock availability
            $stock = $cart->variant ? $cart->variant->stock_quantity : $cart->product->stock_quantity;
            
            if ($stock < $request->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock available'
                ], 400);
            }

            $cart->update([
                'quantity' => $request->quantity
            ]);

            $cartTotal = Auth::user()->cart_total;
            $cartCount = Auth::user()->cart_items_count;
            $itemTotal = $cart->price * $cart->quantity;

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully!',
                'cart_total' => $cartTotal,
                'cart_count' => $cartCount,
                'item_total' => $itemTotal
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function remove(Cart $cart)
    {
        // Check if user owns this cart item
        if ($cart->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action'
            ], 403);
        }

        try {
            $cart->delete();

            $cartTotal = Auth::user()->cart_total;
            $cartCount = Auth::user()->cart_items_count;

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart successfully!',
                'cart_total' => $cartTotal,
                'cart_count' => $cartCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing item from cart: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear user's cart
     */
    public function clear()
    {
        try {
            Auth::user()->carts()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully!',
                'cart_total' => 0,
                'cart_count' => 0
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cart: ' . $e->getMessage()
            ], 500);
        }
    }

   
 
  private function addToDatabaseCart($request, $product)
{
    // Calculate price based on variant or product
    if ($request->variant_id) {
        $variant = ProductVariant::find($request->variant_id);
        $price = $variant->final_price;
    } else {
        $price = $product->final_price;
    }

    // Your existing database cart logic here
    $cartItem = \App\Models\Cart::updateOrCreate(
        [
            'user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'variant_id' => $request->variant_id
        ],
        [
            'quantity' => $request->quantity,
            'price' => $price // Add this line
        ]
    );

    $cartCount = Auth::user()->carts()->count();

    return response()->json([
        'success' => true,
        'message' => 'Product added to cart successfully!',
        'cart_count' => $cartCount
    ]);
}

     private function addToSessionCart($request, $product)
    {
        $cart = session()->get('cart', []);
        
        $key = $request->product_id . '_' . ($request->variant_id ?? '0');
        
        if (isset($cart[$key])) {
            $cart[$key]['quantity'] += $request->quantity;
        } else {
            $cart[$key] = [
                'product_id' => $request->product_id,
                'variant_id' => $request->variant_id,
                'quantity' => $request->quantity,
                'product_name' => $product->name,
                'product_price' => $request->variant_id ? $product->variants->find($request->variant_id)->final_price : $product->final_price,
                'product_image' => $product->images->first()->image_path ?? null
            ];
        }

        session()->put('cart', $cart);
        
        $cartCount = count($cart);

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'cart_count' => $cartCount
        ]);
    }

    public function getCount()
    {
        if (Auth::check()) {
            $count = Auth::user()->cartItems()->count();
        } else {
            $cart = session()->get('cart', []);
            $count = count($cart);
        }

        return response()->json(['count' => $count]);
    }
}