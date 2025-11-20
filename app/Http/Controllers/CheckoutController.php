<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;

class CheckoutController extends Controller
{
    /**
     * Display checkout page
     */
    public function index()
    {
        $user = Auth::user();
        $cartItems = $user->carts()->with(['product.primaryImage', 'variant'])->get();
        
        if ($cartItems->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty!');
        }

        $cartTotal = $user->cart_total;
        $cartCount = $user->cart_items_count;

        return view('checkout.index', compact('cartItems', 'cartTotal', 'cartCount', 'user'));
    }

    /**
     * Process checkout and create order
     */
    public function process(Request $request)
    {
        Log::info('🟢 Checkout process started', ['request' => $request->all()]);

        // Validate request
        $validated = $request->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:15',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'required|string|max:255',
            'shipping_country' => 'required|string|max:255',
            'shipping_pincode' => 'required|string|max:10',
            'billing_same_as_shipping' => 'boolean',
            'billing_name' => 'required_if:billing_same_as_shipping,false|string|max:255',
            'billing_address' => 'required_if:billing_same_as_shipping,false|string',
            'billing_city' => 'required_if:billing_same_as_shipping,false|string|max:255',
            'billing_state' => 'required_if:billing_same_as_shipping,false|string|max:255',
            'billing_country' => 'required_if:billing_same_as_shipping,false|string|max:255',
            'billing_pincode' => 'required_if:billing_same_as_shipping,false|string|max:10',
            'payment_method' => 'required|in:cod,razorpay',
            'customer_note' => 'nullable|string'
        ]);

        try {
            $user = Auth::user();
            Log::info('👤 Authenticated user', ['id' => $user->id, 'email' => $user->email]);

            $cartItems = $user->carts()->with(['product', 'variant'])->get();
            Log::info('🛒 Cart items count', ['count' => $cartItems->count()]);

            if ($cartItems->count() === 0) {
                Log::warning('⚠️ Cart is empty for user', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty!'
                ], 400);
            }

            // Calculate totals
            $subtotal = $user->cart_total;
            $shippingCharge = 0;
            $taxAmount = 0;
            $discountAmount = 0;
            $totalAmount = $subtotal + $shippingCharge + $taxAmount - $discountAmount;

            Log::info('💰 Order totals calculated', [
                'subtotal' => $subtotal,
                'total' => $totalAmount
            ]);

            $shippingAddress = $this->formatAddress($request, 'shipping');
            $billingAddress = $request->billing_same_as_shipping 
                ? $shippingAddress 
                : $this->formatAddress($request, 'billing');

            // Create order
            $order = Order::create([
                'order_number' => 'ORD' . time() . rand(1000, 9999),
                'user_id' => $user->id,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_charge' => $shippingCharge,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => $request->payment_method === 'cod' ? 'pending' : 'pending',
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
                'customer_note' => $request->customer_note,
            ]);

            Log::info('✅ Order created successfully', ['order_id' => $order->id, 'order_number' => $order->order_number]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'variant_id' => $cartItem->variant_id,
                    'product_name' => $cartItem->product->name,
                    'variant_attributes' => $cartItem->variant ? 
                        "Color: {$cartItem->variant->color_name}, Size: {$cartItem->variant->size}" : null,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'total' => $cartItem->price * $cartItem->quantity,
                ]);
            }

            Log::info('📦 Order items added', ['count' => $cartItems->count()]);

            if ($request->payment_method === 'razorpay') {
                Log::info('💳 Razorpay payment flow started');

                try {
                    $razorpay = new \Razorpay\Api\Api(
                        env('RAZORPAY_KEY'),
                        env('RAZORPAY_SECRET')
                    );

                    $razorpayOrderData = [
                        'receipt' => $order->order_number,
                        'amount' => $totalAmount * 100,
                        'currency' => 'INR',
                        'payment_capture' => 1
                    ];

                    $razorpayOrder = $razorpay->order->create($razorpayOrderData);

                    Log::info('🪙 Razorpay order created successfully', [
                        'razorpay_order_id' => $razorpayOrder->id
                    ]);

                    // Create payment record
                    Payment::create([
                        'order_id' => $order->id,
                        'payment_id' => $razorpayOrder->id,
                        'payment_method' => 'razorpay',
                        'amount' => $totalAmount,
                        'currency' => 'INR',
                        'status' => 'pending',
                        'razorpay_order_id' => $razorpayOrder->id,
                    ]);

                    return response()->json([
                        'success' => true,
                        'payment_method' => 'razorpay',
                        'order_id' => $order->id,
                        'razorpay_order_id' => $razorpayOrder->id,
                        'amount' => $totalAmount * 100,
                        'currency' => 'INR',
                        'key' => env('RAZORPAY_KEY'),
                        'name' => config('app.name'),
                        'description' => 'Order Payment',
                        'prefill_name' => $user->name,
                        'prefill_email' => $user->email,
                        'prefill_contact' => $request->shipping_phone,
                    ]);

                } catch (\Exception $razorpayException) {
                    Log::error('❌ Razorpay order creation failed', [
                        'error' => $razorpayException->getMessage()
                    ]);

                    $order->update([
                        'status' => 'failed',
                        'payment_status' => 'failed'
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Payment gateway error: ' . $razorpayException->getMessage()
                    ], 500);
                }

            } else {
                // COD Payment
                Log::info('💵 COD payment selected');

                $order->update([
                    'status' => 'confirmed',
                    'payment_status' => 'pending'
                ]);

                // Clear cart
                $user->carts()->delete();

                Log::info('🧹 Cart cleared after COD order', ['order_id' => $order->id]);

                return response()->json([
                    'success' => true,
                    'payment_method' => 'cod',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'message' => 'Order placed successfully! You can pay cash on delivery.'
                ]);

            }

        } catch (\Exception $e) {
            Log::error('❌ Error during checkout process', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format address from request
     */
    private function formatAddress($request, $type)
    {
        return json_encode([
            'name' => $request->{$type . '_name'},
            'phone' => $request->{$type . '_phone'} ?? $request->shipping_phone,
            'address' => $request->{$type . '_address'},
            'city' => $request->{$type . '_city'},
            'state' => $request->{$type . '_state'},
            'country' => $request->{$type . '_country'},
            'pincode' => $request->{$type . '_pincode'},
        ]);
    }
}