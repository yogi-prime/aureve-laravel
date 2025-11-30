<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ShiprocketService
{
    private string $baseUrl = 'https://apiv2.shiprocket.in/v1/external';
    private ?string $token = null;

    public function __construct()
    {
        $this->token = $this->getToken();
    }

    /**
     * Get authentication token (cached for 24 hours)
     */
    private function getToken(): ?string
    {
        return Cache::remember('shiprocket_token', 60 * 60 * 24, function () {
            try {
                $response = Http::post("{$this->baseUrl}/auth/login", [
                    'email' => config('services.shiprocket.email'),
                    'password' => config('services.shiprocket.password'),
                ]);

                if ($response->successful()) {
                    return $response->json('token');
                }

                Log::error('Shiprocket auth failed', ['response' => $response->json()]);
                return null;
            } catch (\Exception $e) {
                Log::error('Shiprocket auth exception', ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Clear cached token (use when token expires)
     */
    public function clearToken(): void
    {
        Cache::forget('shiprocket_token');
        $this->token = $this->getToken();
    }

    /**
     * Make authenticated request
     */
    private function request(string $method, string $endpoint, array $data = []): array
    {
        if (!$this->token) {
            return ['success' => false, 'message' => 'Authentication failed'];
        }

        try {
            $http = Http::withHeaders([
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json',
            ]);

            $response = match (strtoupper($method)) {
                'GET' => $http->get("{$this->baseUrl}/{$endpoint}", $data),
                'POST' => $http->post("{$this->baseUrl}/{$endpoint}", $data),
                'PUT' => $http->put("{$this->baseUrl}/{$endpoint}", $data),
                'DELETE' => $http->delete("{$this->baseUrl}/{$endpoint}"),
                default => throw new \Exception("Invalid HTTP method: {$method}"),
            };

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            // Token expired - retry once
            if ($response->status() === 401) {
                $this->clearToken();
                return $this->request($method, $endpoint, $data);
            }

            Log::error("Shiprocket API error: {$endpoint}", [
                'status' => $response->status(),
                'response' => $response->json()
            ]);

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'API request failed',
                'errors' => $response->json('errors') ?? []
            ];
        } catch (\Exception $e) {
            Log::error("Shiprocket exception: {$endpoint}", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Create order on Shiprocket
     */
    public function createOrder(Order $order): array
    {
        $shippingAddress = is_string($order->shipping_address)
            ? json_decode($order->shipping_address, true)
            : $order->shipping_address;

        $orderItems = $order->items->map(function ($item) {
            return [
                'name' => $item->product_name,
                'sku' => $item->product->sku ?? "SKU-{$item->product_id}",
                'units' => $item->quantity,
                'selling_price' => $item->price,
                'discount' => 0,
                'tax' => 0,
                'hsn' => $item->product->hsn_code ?? '',
            ];
        })->toArray();

        $data = [
            'order_id' => $order->order_number,
            'order_date' => $order->created_at->format('Y-m-d H:i'),
            'pickup_location' => config('services.shiprocket.pickup_location', 'Primary'),
            'channel_id' => config('services.shiprocket.channel_id', ''),
            'comment' => $order->customer_note ?? '',
            'billing_customer_name' => $shippingAddress['name'] ?? $shippingAddress['first_name'] ?? '',
            'billing_last_name' => $shippingAddress['last_name'] ?? '',
            'billing_address' => $shippingAddress['address'] ?? $shippingAddress['address_line_1'] ?? '',
            'billing_address_2' => $shippingAddress['address_2'] ?? $shippingAddress['address_line_2'] ?? '',
            'billing_city' => $shippingAddress['city'] ?? '',
            'billing_pincode' => $shippingAddress['pincode'] ?? $shippingAddress['postal_code'] ?? '',
            'billing_state' => $shippingAddress['state'] ?? '',
            'billing_country' => $shippingAddress['country'] ?? 'India',
            'billing_email' => $shippingAddress['email'] ?? $order->user->email ?? '',
            'billing_phone' => $shippingAddress['phone'] ?? $shippingAddress['mobile'] ?? '',
            'shipping_is_billing' => true,
            'order_items' => $orderItems,
            'payment_method' => $order->payment_status === 'paid' ? 'Prepaid' : 'COD',
            'shipping_charges' => $order->shipping_charge ?? 0,
            'giftwrap_charges' => 0,
            'transaction_charges' => 0,
            'total_discount' => $order->discount_amount ?? 0,
            'sub_total' => $order->subtotal,
            'length' => 10,
            'breadth' => 10,
            'height' => 10,
            'weight' => $order->shipping_weight ?? 0.5,
        ];

        $result = $this->request('POST', 'orders/create/adhoc', $data);

        if ($result['success'] && isset($result['data']['order_id'])) {
            $order->update([
                'shiprocket_order_id' => $result['data']['order_id'],
                'shiprocket_shipment_id' => $result['data']['shipment_id'] ?? null,
                'shiprocket_response' => $result['data'],
            ]);
        }

        return $result;
    }

    /**
     * Generate AWB (Airway Bill) for shipment
     */
    public function generateAWB(Order $order, int $courierId = null): array
    {
        if (!$order->shiprocket_shipment_id) {
            return ['success' => false, 'message' => 'Shipment ID not found'];
        }

        $data = [
            'shipment_id' => $order->shiprocket_shipment_id,
        ];

        if ($courierId) {
            $data['courier_id'] = $courierId;
        }

        $result = $this->request('POST', 'courier/assign/awb', $data);

        if ($result['success'] && isset($result['data']['response']['data']['awb_code'])) {
            $awbData = $result['data']['response']['data'];
            $order->update([
                'awb_code' => $awbData['awb_code'],
                'courier_name' => $awbData['courier_name'] ?? null,
                'courier_id' => $awbData['courier_company_id'] ?? $courierId,
                'tracking_number' => $awbData['awb_code'],
            ]);
        }

        return $result;
    }

    /**
     * Schedule pickup
     */
    public function schedulePickup(Order $order): array
    {
        if (!$order->shiprocket_shipment_id) {
            return ['success' => false, 'message' => 'Shipment ID not found'];
        }

        $result = $this->request('POST', 'courier/generate/pickup', [
            'shipment_id' => [$order->shiprocket_shipment_id],
        ]);

        if ($result['success']) {
            $order->update([
                'pickup_scheduled_at' => now(),
                'status' => 'processing',
            ]);
        }

        return $result;
    }

    /**
     * Track shipment by AWB
     */
    public function trackByAWB(string $awbCode): array
    {
        return $this->request('GET', "courier/track/awb/{$awbCode}");
    }

    /**
     * Track shipment by Shiprocket shipment ID
     */
    public function trackByShipmentId(int $shipmentId): array
    {
        return $this->request('GET', "courier/track/shipment/{$shipmentId}");
    }

    /**
     * Track order and return formatted timeline
     */
    public function getTrackingTimeline(Order $order): array
    {
        $timeline = [];

        // Order placed - always show
        $timeline[] = [
            'event' => 'Order Placed',
            'description' => 'Your order has been placed successfully.',
            'date' => $order->created_at->toIso8601String(),
            'completed' => true,
            'active' => false,
        ];

        // If no AWB yet, show basic timeline
        if (!$order->awb_code) {
            $timeline[] = [
                'event' => 'Order Confirmed',
                'description' => 'Your order has been confirmed and is being prepared.',
                'date' => $order->created_at->addMinutes(30)->toIso8601String(),
                'completed' => in_array($order->status, ['confirmed', 'processing', 'shipped', 'delivered']),
                'active' => $order->status === 'pending',
            ];

            $timeline[] = [
                'event' => 'Preparing for Shipment',
                'description' => 'Your order is being packed and will be handed over to courier soon.',
                'date' => null,
                'completed' => false,
                'active' => in_array($order->status, ['confirmed', 'processing']),
            ];

            $timeline[] = [
                'event' => 'Shipped',
                'description' => 'Waiting for shipment.',
                'date' => null,
                'completed' => false,
                'active' => false,
            ];

            $timeline[] = [
                'event' => 'Delivered',
                'description' => 'Waiting for delivery.',
                'date' => null,
                'completed' => false,
                'active' => false,
            ];

            return [
                'success' => true,
                'timeline' => $timeline,
                'tracking_info' => null,
            ];
        }

        // Fetch real tracking data from Shiprocket
        $trackingResult = $this->trackByAWB($order->awb_code);

        if (!$trackingResult['success']) {
            // Return basic timeline on error
            return [
                'success' => false,
                'timeline' => $timeline,
                'tracking_info' => null,
                'message' => 'Unable to fetch tracking details',
            ];
        }

        $trackingData = $trackingResult['data']['tracking_data'] ?? [];
        $shipmentTrack = $trackingData['shipment_track'] ?? [];
        $shipmentStatus = $trackingData['shipment_status'] ?? null;

        // Add confirmed step
        $timeline[] = [
            'event' => 'Order Confirmed',
            'description' => 'Your order has been confirmed.',
            'date' => $order->created_at->addMinutes(30)->toIso8601String(),
            'completed' => true,
            'active' => false,
        ];

        // Shipped
        $shippedActivity = collect($shipmentTrack)->first(function ($track) {
            return str_contains(strtolower($track['activity'] ?? ''), 'picked') ||
                   str_contains(strtolower($track['activity'] ?? ''), 'shipped') ||
                   str_contains(strtolower($track['sr-status-label'] ?? ''), 'shipped');
        });

        $isShipped = $shippedActivity || in_array($shipmentStatus, [6, 7, 8, 17, 18, 19, 20, 38, 39, 40, 41, 42]);
        $timeline[] = [
            'event' => 'Shipped',
            'description' => $order->courier_name
                ? "Shipped via {$order->courier_name}. AWB: {$order->awb_code}"
                : "Your order has been shipped. AWB: {$order->awb_code}",
            'date' => $shippedActivity['date'] ?? $order->shipped_at?->toIso8601String(),
            'completed' => $isShipped,
            'active' => !$isShipped && $order->awb_code,
        ];

        // In Transit
        $inTransitActivity = collect($shipmentTrack)->first(function ($track) {
            return str_contains(strtolower($track['activity'] ?? ''), 'transit') ||
                   str_contains(strtolower($track['activity'] ?? ''), 'hub');
        });

        $isInTransit = $inTransitActivity || in_array($shipmentStatus, [17, 18, 19, 20, 38, 39, 40, 41]);
        if ($isShipped) {
            $timeline[] = [
                'event' => 'In Transit',
                'description' => $inTransitActivity['activity'] ?? 'Your order is on the way.',
                'date' => $inTransitActivity['date'] ?? null,
                'completed' => $isInTransit,
                'active' => $isShipped && !$isInTransit,
            ];
        }

        // Out for Delivery
        $outForDeliveryActivity = collect($shipmentTrack)->first(function ($track) {
            return str_contains(strtolower($track['activity'] ?? ''), 'out for delivery') ||
                   str_contains(strtolower($track['sr-status-label'] ?? ''), 'out for delivery');
        });

        $isOutForDelivery = $outForDeliveryActivity || $shipmentStatus === 17;
        if ($isInTransit || $isOutForDelivery) {
            $timeline[] = [
                'event' => 'Out for Delivery',
                'description' => 'Your order is out for delivery.',
                'date' => $outForDeliveryActivity['date'] ?? null,
                'completed' => $isOutForDelivery,
                'active' => $isInTransit && !$isOutForDelivery,
            ];
        }

        // Delivered
        $deliveredActivity = collect($shipmentTrack)->first(function ($track) {
            return str_contains(strtolower($track['activity'] ?? ''), 'delivered') ||
                   str_contains(strtolower($track['sr-status-label'] ?? ''), 'delivered');
        });

        $isDelivered = $deliveredActivity || $shipmentStatus === 7;
        $timeline[] = [
            'event' => 'Delivered',
            'description' => $isDelivered ? 'Your order has been delivered successfully!' : 'Waiting for delivery.',
            'date' => $deliveredActivity['date'] ?? $order->delivered_at?->toIso8601String(),
            'completed' => $isDelivered,
            'active' => $isOutForDelivery && !$isDelivered,
        ];

        // Update order status if delivered
        if ($isDelivered && $order->status !== 'delivered') {
            $order->update([
                'status' => 'delivered',
                'delivered_at' => $deliveredActivity['date'] ?? now(),
            ]);
        }

        return [
            'success' => true,
            'timeline' => $timeline,
            'tracking_info' => [
                'awb_code' => $order->awb_code,
                'courier_name' => $order->courier_name,
                'current_status' => $trackingData['shipment_track_activities'][0]['activity'] ?? $shipmentStatus,
                'current_location' => $trackingData['shipment_track_activities'][0]['location'] ?? null,
                'expected_delivery' => $trackingData['etd'] ?? null,
                'track_url' => $trackingData['track_url'] ?? null,
                'activities' => array_slice($trackingData['shipment_track_activities'] ?? [], 0, 10),
            ],
        ];
    }

    /**
     * Get available couriers for shipment
     */
    public function getAvailableCouriers(Order $order): array
    {
        $shippingAddress = is_string($order->shipping_address)
            ? json_decode($order->shipping_address, true)
            : $order->shipping_address;

        $data = [
            'pickup_postcode' => config('services.shiprocket.pickup_pincode'),
            'delivery_postcode' => $shippingAddress['pincode'] ?? $shippingAddress['postal_code'] ?? '',
            'weight' => $order->shipping_weight ?? 0.5,
            'cod' => $order->payment_status !== 'paid' ? 1 : 0,
        ];

        return $this->request('GET', 'courier/serviceability', $data);
    }

    /**
     * Cancel shipment
     */
    public function cancelShipment(Order $order): array
    {
        if (!$order->awb_code) {
            return ['success' => false, 'message' => 'No AWB found'];
        }

        $result = $this->request('POST', 'orders/cancel', [
            'ids' => [$order->shiprocket_order_id],
        ]);

        if ($result['success']) {
            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        }

        return $result;
    }

    /**
     * Get order manifest/label
     */
    public function generateLabel(Order $order): array
    {
        if (!$order->shiprocket_shipment_id) {
            return ['success' => false, 'message' => 'Shipment ID not found'];
        }

        return $this->request('POST', 'courier/generate/label', [
            'shipment_id' => [$order->shiprocket_shipment_id],
        ]);
    }

    /**
     * Get invoice
     */
    public function generateInvoice(Order $order): array
    {
        if (!$order->shiprocket_order_id) {
            return ['success' => false, 'message' => 'Shiprocket order ID not found'];
        }

        return $this->request('POST', 'orders/print/invoice', [
            'ids' => [$order->shiprocket_order_id],
        ]);
    }
}
