<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Payment;

class PaymentController extends Controller
{
    /**
     * Handle Razorpay payment success
     */
    public function razorpaySuccess(Request $request)
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'order_id' => 'required|exists:orders,id'
        ]);

        try {
            // Verify payment signature
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

            // Update payment and order
            $payment = Payment::where('razorpay_order_id', $request->razorpay_order_id)->firstOrFail();
            $order = $payment->order;

            $payment->update([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
                'status' => 'completed',
                'paid_at' => now(),
                'payment_response' => json_encode($attributes)
            ]);

            $order->update([
                'status' => 'confirmed',
                'payment_status' => 'paid'
            ]);

            // Clear user's cart
            Auth::user()->carts()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! Your order has been placed.',
                'order_number' => $order->order_number
            ]);

        } catch (\Exception $e) {
            Log::error('Razorpay payment error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Handle Razorpay payment failure
     */
    public function razorpayFailure(Request $request)
    {
        try {
            $order = Order::findOrFail($request->order_id);
            
            // Update order status to failed
            $order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed'
            ]);

            // Update payment status
            $payment = $order->payment;
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                    'payment_response' => json_encode($request->all())
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Please try again.'
            ]);

        } catch (\Exception $e) {
            Log::error('Razorpay failure error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error processing payment failure.'
            ], 500);
        }
    }

    /**
     * Razorpay webhook handler
     */
    public function razorpayWebhook(Request $request)
    {
        $webhookSecret = env('RAZORPAY_WEBHOOK_SECRET');
        
        if (!$webhookSecret) {
            Log::error('Razorpay webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        // Verify webhook signature
        $razorpaySignature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        if ($razorpaySignature !== $expectedSignature) {
            Log::error('Razorpay webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $payload = $request->input('payload');

        Log::info('Razorpay webhook received', ['event' => $event]);

        try {
            switch ($event) {
                case 'payment.captured':
                    $this->handlePaymentCaptured($payload['payment']['entity']);
                    break;
                
                case 'payment.failed':
                    $this->handlePaymentFailed($payload['payment']['entity']);
                    break;
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Razorpay webhook error: ' . $e->getMessage());
            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    private function handlePaymentCaptured($payment)
    {
        $orderPayment = Payment::where('razorpay_payment_id', $payment['id'])->first();
        
        if ($orderPayment) {
            $orderPayment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'payment_response' => json_encode($payment)
            ]);

            $orderPayment->order->update([
                'status' => 'confirmed',
                'payment_status' => 'paid'
            ]);

            Log::info('Payment captured via webhook', ['payment_id' => $payment['id']]);
        }
    }

    private function handlePaymentFailed($payment)
    {
        $orderPayment = Payment::where('razorpay_payment_id', $payment['id'])->first();
        
        if ($orderPayment) {
            $orderPayment->update([
                'status' => 'failed',
                'payment_response' => json_encode($payment)
            ]);

            $orderPayment->order->update([
                'status' => 'cancelled',
                'payment_status' => 'failed'
            ]);

            Log::info('Payment failed via webhook', ['payment_id' => $payment['id']]);
        }
    }
}