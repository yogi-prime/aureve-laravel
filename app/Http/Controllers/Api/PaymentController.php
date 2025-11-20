<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function razorpaySuccess(Request $request)
    {
        Log::info('✅ Razorpay payment success callback', $request->all());

        $validator = \Validator::make($request->all(), [
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'order_id' => 'required|exists:orders,id'
        ]);

        if ($validator->fails()) {
            Log::error('❌ Razorpay success validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid payment data'
            ], 400);
        }

        try {
            $razorpay = new \Razorpay\Api\Api(
                env('RAZORPAY_KEY'),
                env('RAZORPAY_SECRET')
            );

            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature
            ];

            $razorpay->utility->verifyPaymentSignature($attributes);

            $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->firstOrFail();
            $order = $payment->order;

            $payment->update([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'status' => 'completed',
                'paid_at' => now(),
            ]);

            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'paid'
            ]);

            // Clear user's cart
            Auth::user()->carts()->delete();

            Log::info('🎉 Payment completed successfully', [
                'order_id' => $order->id,
                'payment_id' => $payment->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Your order has been placed.',
                'order_number' => $order->order_number
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Razorpay payment verification failed', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 400);
        }
    }

    public function razorpayFailure(Request $request)
    {
        Log::info('❌ Razorpay payment failure', $request->all());

        try {
            $order = Order::findOrFail($request->order_id);
            
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed'
            ]);

            $payment = $order->payment;
            if ($payment) {
                $payment->update([
                    'status' => 'failed'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Please try again.'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Razorpay failure processing error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing payment failure.'
            ], 500);
        }
    }
}