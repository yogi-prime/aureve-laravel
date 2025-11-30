<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        Log::info('📦 Orders list accessed', ['user_id' => Auth::id()]);

        try {
            $orders = Auth::user()->orders()
                ->with(['items.product.primaryImage', 'payment'])
                ->latest()
                ->paginate(10);

            Log::info('✅ Orders fetched successfully', [
                'user_id' => Auth::id(),
                'count' => $orders->count()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orders->items(),
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage(),
                        'per_page' => $orders->perPage(),
                        'total' => $orders->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Failed to fetch orders', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders'
            ], 500);
        }
    }

    public function show(Order $order)
    {
        Log::info('📋 Order details accessed', [
            'user_id' => Auth::id(),
            'order_id' => $order->id
        ]);

        try {
            if ($order->user_id !== Auth::id()) {
                Log::warning('🚫 Unauthorized order access attempt', [
                    'user_id' => Auth::id(),
                    'order_user_id' => $order->user_id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action'
                ], 403);
            }

            $order->load(['items.product.primaryImage', 'items.variant', 'payment']);

            return response()->json([
                'success' => true,
                'data' => $order
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Failed to fetch order details', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order details'
            ], 500);
        }
    }

    public function track(Order $order)
    {
        try {
            if ($order->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action'
                ], 403);
            }

            $order->load(['items.product.primaryImage']);

            // Use Shiprocket tracking if available
            $shiprocket = new ShiprocketService();
            $trackingData = $shiprocket->getTrackingTimeline($order);

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'total_amount' => $order->total_amount,
                        'created_at' => $order->created_at,
                        'items' => $order->items,
                        'tracking_number' => $order->tracking_number,
                        'awb_code' => $order->awb_code,
                        'courier_name' => $order->courier_name,
                    ],
                    'timeline' => $trackingData['timeline'],
                    'tracking_info' => $trackingData['tracking_info'] ?? null,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Failed to track order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to track order'
            ], 500);
        }
    }

    public function cancel(Request $request, Order $order)
    {
        Log::info('❌ Order cancellation requested', [
            'user_id' => Auth::id(),
            'order_id' => $order->id
        ]);

        try {
            if ($order->user_id !== Auth::id()) {
                Log::warning('🚫 Unauthorized order cancellation attempt', [
                    'user_id' => Auth::id(),
                    'order_user_id' => $order->user_id
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action'
                ], 403);
            }

            if (!$order->canBeCancelled()) {
                Log::warning('⚠️ Order cannot be cancelled', [
                    'order_id' => $order->id,
                    'status' => $order->status
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'This order cannot be cancelled.'
                ], 400);
            }

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            Log::info('✅ Order cancelled successfully', ['order_id' => $order->id]);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Order cancellation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error cancelling order: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getOrderTimeline(Order $order)
    {
        $timeline = [];

        $timeline[] = [
            'event' => 'Order Placed',
            'description' => 'Your order has been placed successfully.',
            'date' => $order->created_at,
            'completed' => true,
            'active' => false
        ];

        if (in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered'])) {
            $timeline[] = [
                'event' => 'Order Confirmed',
                'description' => 'Your order has been confirmed.',
                'date' => $order->created_at->addMinutes(30),
                'completed' => true,
                'active' => false
            ];
        }

        if (in_array($order->status, ['processing', 'shipped', 'delivered'])) {
            $timeline[] = [
                'event' => 'Processing',
                'description' => 'Your order is being processed.',
                'date' => $order->created_at->addHours(2),
                'completed' => true,
                'active' => false
            ];
        }

        if (in_array($order->status, ['shipped', 'delivered'])) {
            $timeline[] = [
                'event' => 'Shipped',
                'description' => 'Your order has been shipped.',
                'date' => $order->shipped_at ?? $order->created_at->addDays(1),
                'completed' => true,
                'active' => false
            ];
        }

        if ($order->status === 'delivered') {
            $timeline[] = [
                'event' => 'Delivered',
                'description' => 'Your order has been delivered.',
                'date' => $order->delivered_at,
                'completed' => true,
                'active' => false
            ];
        }

        // Set current active step
        foreach ($timeline as &$item) {
            if (!$item['completed']) {
                $item['active'] = true;
                break;
            }
        }

        return $timeline;
    }
}