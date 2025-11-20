<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display user's orders
     */
    public function index()
    {
        $orders = Auth::user()->orders()
            ->with(['items.product.primaryImage', 'payment'])
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Display single order details
     */
    public function show(Order $order)
    {
        // Check if user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['items.product.primaryImage', 'items.variant', 'payment']);

        return view('orders.show', compact('order'));
    }

    /**
     * Cancel order
     */
    public function cancel(Order $order)
    {
        // Check if user owns this order
        if ($order->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        // Check if order can be cancelled
        if (!$order->can_be_cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be cancelled.'
            ], 400);
        }

        try {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            // If payment was made, handle refund (you can implement this later)
            if ($order->payment_status === 'paid') {
                // Initiate refund process
            }

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track order
     */
    public function track(Order $order)
    {
        // Check if user owns this order
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['items.product.primaryImage']);

        $timeline = $this->getOrderTimeline($order);

        return view('orders.track', compact('order', 'timeline'));
    }

    /**
     * Get order timeline for tracking
     */
    private function getOrderTimeline(Order $order)
    {
        $timeline = [];

        // Order placed
        $timeline[] = [
            'event' => 'Order Placed',
            'description' => 'Your order has been placed successfully.',
            'date' => $order->created_at,
            'completed' => true,
            'active' => false
        ];

        // Order confirmed
        if (in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered'])) {
            $timeline[] = [
                'event' => 'Order Confirmed',
                'description' => 'Your order has been confirmed.',
                'date' => $order->created_at->addMinutes(30),
                'completed' => true,
                'active' => false
            ];
        }

        // Processing
        if (in_array($order->status, ['processing', 'shipped', 'delivered'])) {
            $timeline[] = [
                'event' => 'Processing',
                'description' => 'Your order is being processed.',
                'date' => $order->created_at->addHours(2),
                'completed' => true,
                'active' => false
            ];
        }

        // Shipped
        if (in_array($order->status, ['shipped', 'delivered'])) {
            $timeline[] = [
                'event' => 'Shipped',
                'description' => 'Your order has been shipped.',
                'date' => $order->shipped_at,
                'completed' => true,
                'active' => false
            ];
        }

        // Delivered
        if ($order->status === 'delivered') {
            $timeline[] = [
                'event' => 'Delivered',
                'description' => 'Your order has been delivered.',
                'date' => $order->delivered_at,
                'completed' => true,
                'active' => false
            ];
        }

        // Current step
        foreach ($timeline as &$item) {
            if (!$item['completed']) {
                $item['active'] = true;
                break;
            }
        }

        return $timeline;
    }
}