<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        Log::info('🛒 Checkout page accessed', ['user_id' => Auth::id()]);

        try {
            $user = Auth::user();
            $cartItems = $user->carts()->with(['product.primaryImage', 'variant'])->get();

            if ($cartItems->count() === 0) {
                Log::warning('⚠️ Empty cart during checkout', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty!'
                ], 400);
            }

            $cartTotal = $user->cart_total;
            $cartCount = $user->cart_items_count;

            Log::info('✅ Checkout data fetched successfully', [
                'user_id' => $user->id,
                'cart_items_count' => $cartCount,
                'cart_total' => $cartTotal
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'cart_items' => $cartItems,
                    'cart_total' => $cartTotal,
                    'cart_count' => $cartCount,
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'city' => $user->city,
                        'state' => $user->state,
                        'country' => $user->country,
                        'pincode' => $user->pincode,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Checkout index failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load checkout page'
            ], 500);
        }
    }

    public function process(Request $request)
    {
        Log::info('🟢 Checkout process started', [
            'user_id' => Auth::id(),
            'payment_method' => $request->payment_method
        ]);

        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            Log::warning('⚠️ Checkout validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $cartItems = $user->carts()->with(['product', 'variant'])->get();

            if ($cartItems->count() === 0) {
                Log::warning('⚠️ Cart is empty during checkout process', ['user_id' => $user->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty!'
                ], 400);
            }

            // Calculate totals
            $subtotal = $user->cart_total;
            $shippingCharge = 0;
            $taxAmount = $subtotal * 0.18; // 18% GST
            $discountAmount = 0;
            $totalAmount = $subtotal + $shippingCharge + $taxAmount - $discountAmount;

            Log::info('💰 Order totals calculated', [
                'subtotal' => $subtotal,
                'tax' => $taxAmount,
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
                'payment_status' => 'pending',
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
                'customer_note' => $request->customer_note,
            ]);

            Log::info('✅ Order created', [
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'variant_id' => $cartItem->variant_id,
                    'product_name' => $cartItem->product->name,
                    'variant_attributes' => $cartItem->variant ? 
                        "Color: {$cartItem->variant->color_name}, Size: {$cartItem->variant->size_name}" : null,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'total' => $cartItem->price * $cartItem->quantity,
                ]);
            }

            Log::info('📦 Order items created', ['count' => $cartItems->count()]);

            if ($request->payment_method === 'razorpay') {
                return $this->processRazorpayPayment($order, $totalAmount, $user, $request);
            } else {
                return $this->processCODPayment($order, $user);
            }

        } catch (\Exception $e) {
            Log::error('❌ Checkout process failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing order: ' . $e->getMessage()
            ], 500);
        }
    }

   private function processRazorpayPayment($order, $totalAmount, $user, $request)
{
    try {
        Log::info('💳 Processing Razorpay payment', ['order_id' => $order->id]);

        $razorpay = new \Razorpay\Api\Api(
            env('RAZORPAY_KEY'),
            env('RAZORPAY_SECRET')
        );

        $razorpayOrderData = [
            'receipt' => $order->order_number,
            'amount' => $totalAmount * 100, // Convert to paise
            'currency' => 'INR',
            'payment_capture' => 1
        ];

        $razorpayOrder = $razorpay->order->create($razorpayOrderData);

        Log::info('🪙 Razorpay order created', [
            'razorpay_order_id' => $razorpayOrder->id
        ]);

        // ✅ FIX: Add payment_id like in web version
        Payment::create([
            'order_id' => $order->id,
            'payment_id' => $razorpayOrder->id, // ✅ YEH LINE ADD KARO
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

    } catch (\Exception $e) {
        Log::error('❌ Razorpay payment failed', [
            'order_id' => $order->id,
            'error' => $e->getMessage()
        ]);

        $order->update([
            'status' => 'failed',
            'payment_status' => 'failed'
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment gateway error: ' . $e->getMessage()
        ], 500);
    }
}

    private function processCODPayment($order, $user)
    {
        Log::info('💵 Processing COD payment', ['order_id' => $order->id]);

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