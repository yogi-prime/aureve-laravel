<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\ShiprocketService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShiprocketController extends Controller
{
    protected ShiprocketService $shiprocket;

    public function __construct(ShiprocketService $shiprocket)
    {
        $this->shiprocket = $shiprocket;
    }

    /**
     * Create order on Shiprocket
     */
    public function createOrder(Order $order): JsonResponse
    {
        if ($order->shiprocket_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'Order already exists on Shiprocket',
            ], 400);
        }

        $result = $this->shiprocket->createOrder($order);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Order created on Shiprocket successfully',
                'data' => [
                    'shiprocket_order_id' => $order->fresh()->shiprocket_order_id,
                    'shiprocket_shipment_id' => $order->fresh()->shiprocket_shipment_id,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to create order on Shiprocket',
            'errors' => $result['errors'] ?? [],
        ], 400);
    }

    /**
     * Get available couriers for order
     */
    public function getAvailableCouriers(Order $order): JsonResponse
    {
        $result = $this->shiprocket->getAvailableCouriers($order);

        if ($result['success']) {
            $couriers = $result['data']['data']['available_courier_companies'] ?? [];

            return response()->json([
                'success' => true,
                'data' => collect($couriers)->map(function ($courier) {
                    return [
                        'id' => $courier['courier_company_id'],
                        'name' => $courier['courier_name'],
                        'rate' => $courier['rate'],
                        'etd' => $courier['etd'],
                        'estimated_delivery_days' => $courier['estimated_delivery_days'],
                        'cod' => $courier['cod'] ?? false,
                        'rating' => $courier['rating'] ?? null,
                    ];
                })->toArray(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to fetch couriers',
        ], 400);
    }

    /**
     * Assign AWB to order
     */
    public function assignAWB(Request $request, Order $order): JsonResponse
    {
        if (!$order->shiprocket_shipment_id) {
            // First create order on Shiprocket
            $createResult = $this->shiprocket->createOrder($order);
            if (!$createResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order on Shiprocket',
                ], 400);
            }
            $order->refresh();
        }

        $courierId = $request->input('courier_id');
        $result = $this->shiprocket->generateAWB($order, $courierId);

        if ($result['success']) {
            $order->refresh();
            return response()->json([
                'success' => true,
                'message' => 'AWB assigned successfully',
                'data' => [
                    'awb_code' => $order->awb_code,
                    'courier_name' => $order->courier_name,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to assign AWB',
        ], 400);
    }

    /**
     * Schedule pickup
     */
    public function schedulePickup(Order $order): JsonResponse
    {
        if (!$order->awb_code) {
            return response()->json([
                'success' => false,
                'message' => 'AWB not assigned yet',
            ], 400);
        }

        $result = $this->shiprocket->schedulePickup($order);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Pickup scheduled successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to schedule pickup',
        ], 400);
    }

    /**
     * Ship order (create + AWB + pickup in one go)
     */
    public function shipOrder(Request $request, Order $order): JsonResponse
    {
        // Step 1: Create order on Shiprocket
        if (!$order->shiprocket_order_id) {
            $createResult = $this->shiprocket->createOrder($order);
            if (!$createResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order: ' . ($createResult['message'] ?? ''),
                    'step' => 'create_order',
                ], 400);
            }
            $order->refresh();
        }

        // Step 2: Assign AWB
        if (!$order->awb_code) {
            $courierId = $request->input('courier_id');
            $awbResult = $this->shiprocket->generateAWB($order, $courierId);
            if (!$awbResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to assign AWB: ' . ($awbResult['message'] ?? ''),
                    'step' => 'assign_awb',
                ], 400);
            }
            $order->refresh();
        }

        // Step 3: Schedule pickup
        $pickupResult = $this->shiprocket->schedulePickup($order);
        if (!$pickupResult['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Order created but pickup scheduling failed: ' . ($pickupResult['message'] ?? ''),
                'step' => 'schedule_pickup',
                'data' => [
                    'awb_code' => $order->awb_code,
                    'courier_name' => $order->courier_name,
                ],
            ], 400);
        }

        // Update order status
        $order->update([
            'status' => 'shipped',
            'shipped_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order shipped successfully',
            'data' => [
                'awb_code' => $order->awb_code,
                'courier_name' => $order->courier_name,
                'tracking_number' => $order->tracking_number,
            ],
        ]);
    }

    /**
     * Track shipment
     */
    public function track(Order $order): JsonResponse
    {
        $result = $this->shiprocket->getTrackingTimeline($order);

        return response()->json([
            'success' => $result['success'],
            'data' => $result,
        ]);
    }

    /**
     * Cancel shipment
     */
    public function cancelShipment(Order $order): JsonResponse
    {
        if (!$order->shiprocket_order_id) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found on Shiprocket',
            ], 400);
        }

        $result = $this->shiprocket->cancelShipment($order);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Shipment cancelled successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to cancel shipment',
        ], 400);
    }

    /**
     * Generate shipping label
     */
    public function generateLabel(Order $order): JsonResponse
    {
        $result = $this->shiprocket->generateLabel($order);

        if ($result['success'] && isset($result['data']['label_url'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'label_url' => $result['data']['label_url'],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to generate label',
        ], 400);
    }

    /**
     * Generate invoice
     */
    public function generateInvoice(Order $order): JsonResponse
    {
        $result = $this->shiprocket->generateInvoice($order);

        if ($result['success'] && isset($result['data']['invoice_url'])) {
            return response()->json([
                'success' => true,
                'data' => [
                    'invoice_url' => $result['data']['invoice_url'],
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Failed to generate invoice',
        ], 400);
    }
}
