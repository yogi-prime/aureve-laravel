@extends('layouts.admin')

@section('title', 'Order Details - ' . $order->order_number)
@section('page-title', 'Order: ' . $order->order_number)

@section('header-actions')
    <a href="{{ route('admin.orders.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
        <i class="fas fa-arrow-left mr-2"></i>Back to Orders
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Order Details -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Order Items</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-center gap-4 pb-4 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                            @if($item->product && $item->product->primaryImage)
                                <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}"
                                     alt="{{ $item->product_name }}"
                                     class="w-16 h-16 rounded-lg object-cover">
                            @else
                                <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-box text-gray-400"></i>
                                </div>
                            @endif
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $item->product_name }}</div>
                                @if($item->variant_attributes)
                                    <div class="text-sm text-gray-500">{{ $item->variant_attributes }}</div>
                                @endif
                                <div class="text-sm text-gray-500">Qty: {{ $item->quantity }} x Rs. {{ number_format($item->price, 2) }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-gray-900">Rs. {{ number_format($item->total, 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Order Summary -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Subtotal</span>
                            <span class="text-gray-900">Rs. {{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tax (GST)</span>
                            <span class="text-gray-900">Rs. {{ number_format($order->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Shipping</span>
                            <span class="text-gray-900">Rs. {{ number_format($order->shipping_charge, 2) }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Discount</span>
                                <span class="text-green-600">-Rs. {{ number_format($order->discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                            <span>Total</span>
                            <span>Rs. {{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Address -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Shipping Address</h3>
            </div>
            <div class="p-6">
                @php
                    $shipping = is_string($order->shipping_address) ? json_decode($order->shipping_address, true) : $order->shipping_address;
                @endphp
                @if($shipping)
                    <div class="text-gray-900">
                        <div class="font-medium">{{ $shipping['name'] ?? '-' }}</div>
                        <div class="text-sm text-gray-600 mt-1">{{ $shipping['phone'] ?? '-' }}</div>
                        <div class="text-sm text-gray-600 mt-2">
                            {{ $shipping['address'] ?? '-' }}<br>
                            {{ $shipping['city'] ?? '-' }}, {{ $shipping['state'] ?? '-' }} - {{ $shipping['pincode'] ?? '-' }}<br>
                            {{ $shipping['country'] ?? 'India' }}
                        </div>
                    </div>
                @else
                    <p class="text-gray-500">No shipping address available</p>
                @endif
            </div>
        </div>

        <!-- Customer Note -->
        @if($order->customer_note)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Customer Note</h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-600">{{ $order->customer_note }}</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column - Status & Actions -->
    <div class="space-y-6">
        <!-- Customer Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Customer</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-user text-indigo-600"></i>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">{{ $order->user->name ?? 'Guest' }}</div>
                        <div class="text-sm text-gray-500">{{ $order->user->email ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Order Status</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <select name="status" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ $order->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="delivered" {{ $order->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Payment Status</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.orders.update-payment-status', $order) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <select name="payment_status" onchange="this.form.submit()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="pending" {{ $order->payment_status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ $order->payment_status == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="failed" {{ $order->payment_status == 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="refunded" {{ $order->payment_status == 'refunded' ? 'selected' : '' }}>Refunded</option>
                    </select>
                </form>

                @if($order->payment)
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="text-sm space-y-1">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Method</span>
                                <span class="text-gray-900">{{ ucfirst($order->payment->payment_method) }}</span>
                            </div>
                            @if($order->payment->razorpay_payment_id)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Payment ID</span>
                                    <span class="text-gray-900 text-xs">{{ $order->payment->razorpay_payment_id }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Shiprocket Integration -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    <i class="fas fa-shipping-fast text-indigo-600 mr-2"></i>Shiprocket
                </h3>
                @if($order->shiprocket_order_id)
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Synced</span>
                @else
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Not Synced</span>
                @endif
            </div>
            <div class="p-6">
                @if($order->shiprocket_order_id)
                    <!-- Order is synced with Shiprocket -->
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Shiprocket Order ID</span>
                            <span class="text-gray-900">{{ $order->shiprocket_order_id }}</span>
                        </div>
                        @if($order->shiprocket_shipment_id)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Shipment ID</span>
                                <span class="text-gray-900">{{ $order->shiprocket_shipment_id }}</span>
                            </div>
                        @endif
                        @if($order->awb_code)
                            <div class="flex justify-between">
                                <span class="text-gray-500">AWB Code</span>
                                <span class="text-gray-900 font-medium">{{ $order->awb_code }}</span>
                            </div>
                        @endif
                        @if($order->courier_name)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Courier</span>
                                <span class="text-gray-900">{{ $order->courier_name }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Shiprocket Actions -->
                    <div class="mt-4 pt-4 border-t border-gray-200 space-y-2">
                        @if(!$order->awb_code)
                            <form action="{{ route('admin.shiprocket.awb', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">
                                    <i class="fas fa-barcode mr-2"></i>Generate AWB
                                </button>
                            </form>
                        @else
                            <a href="{{ route('admin.shiprocket.track', $order) }}" target="_blank"
                               class="block w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm text-center">
                                <i class="fas fa-map-marker-alt mr-2"></i>Track Shipment
                            </a>
                            <a href="{{ route('admin.shiprocket.label', $order) }}" target="_blank"
                               class="block w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm text-center">
                                <i class="fas fa-tag mr-2"></i>Download Label
                            </a>
                            <a href="{{ route('admin.shiprocket.invoice', $order) }}" target="_blank"
                               class="block w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 text-sm text-center">
                                <i class="fas fa-file-invoice mr-2"></i>Download Invoice
                            </a>
                        @endif

                        @if($order->status !== 'cancelled' && $order->status !== 'delivered')
                            <form action="{{ route('admin.shiprocket.cancel', $order) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to cancel this shipment?')">
                                @csrf
                                <button type="submit" class="w-full bg-red-100 text-red-700 px-4 py-2 rounded-lg hover:bg-red-200 text-sm">
                                    <i class="fas fa-times mr-2"></i>Cancel Shipment
                                </button>
                            </form>
                        @endif
                    </div>
                @else
                    <!-- Order not synced - Show ship button -->
                    <div class="text-center py-4">
                        <i class="fas fa-truck text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500 mb-4">Order not yet synced with Shiprocket</p>

                        @if(in_array($order->status, ['confirmed', 'processing']))
                            <form action="{{ route('admin.shiprocket.ship', $order) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                                    <i class="fas fa-shipping-fast mr-2"></i>Ship Order via Shiprocket
                                </button>
                            </form>
                            <p class="text-xs text-gray-400 mt-2">This will create order, assign AWB & schedule pickup</p>
                        @else
                            <p class="text-sm text-yellow-600">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Change status to "Confirmed" or "Processing" to ship
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Timeline -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Timeline</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex gap-3">
                        <div class="w-2 h-2 mt-2 rounded-full bg-green-500"></div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">Order Placed</div>
                            <div class="text-xs text-gray-500">{{ $order->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                    </div>
                    @if($order->shipped_at)
                        <div class="flex gap-3">
                            <div class="w-2 h-2 mt-2 rounded-full bg-purple-500"></div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">Shipped</div>
                                <div class="text-xs text-gray-500">{{ $order->shipped_at->format('d M Y, h:i A') }}</div>
                            </div>
                        </div>
                    @endif
                    @if($order->delivered_at)
                        <div class="flex gap-3">
                            <div class="w-2 h-2 mt-2 rounded-full bg-green-500"></div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">Delivered</div>
                                <div class="text-xs text-gray-500">{{ $order->delivered_at->format('d M Y, h:i A') }}</div>
                            </div>
                        </div>
                    @endif
                    @if($order->cancelled_at)
                        <div class="flex gap-3">
                            <div class="w-2 h-2 mt-2 rounded-full bg-red-500"></div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">Cancelled</div>
                                <div class="text-xs text-gray-500">{{ $order->cancelled_at->format('d M Y, h:i A') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
