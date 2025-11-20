@extends('layouts.app')

@section('title', 'Order Details - Ecommerce Store')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Order Details</h1>
                <p class="text-gray-600 mt-2">Order #{{ $order->order_number }}</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                    @if($order->status === 'delivered') bg-green-100 text-green-800
                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                    @elseif($order->status === 'shipped') bg-blue-100 text-blue-800
                    @elseif($order->status === 'processing') bg-yellow-100 text-yellow-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
        </div>
        <p class="text-gray-500 mt-2">
            Placed on {{ $order->created_at->format('F d, Y \\a\\t h:i A') }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Order Items -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Items</h2>
                
                <div class="space-y-4">
                    @foreach($order->items as $item)
                        <div class="flex items-center space-x-4 py-4 border-b border-gray-200 last:border-b-0">
                            @if($item->product->primaryImage)
                                <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" 
                                     alt="{{ $item->product->name }}"
                                     class="w-16 h-16 object-cover rounded">
                            @else
                                <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-medium text-gray-900">{{ $item->product_name }}</h3>
                                @if($item->variant_attributes)
                                    <p class="text-sm text-gray-500 mt-1">{{ $item->variant_attributes }}</p>
                                @endif
                                <p class="text-sm text-gray-500">Quantity: {{ $item->quantity }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-semibold text-gray-900">₹{{ $item->total }}</p>
                                <p class="text-sm text-gray-500">₹{{ $item->price }} each</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-medium">₹{{ $order->subtotal }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shipping</span>
                        <span class="font-medium">₹{{ $order->shipping_charge }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax</span>
                        <span class="font-medium">₹{{ $order->tax_amount }}</span>
                    </div>
                    
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Discount</span>
                        <span>-₹{{ $order->discount_amount }}</span>
                    </div>
                    @endif
                    
                    <div class="border-t border-gray-200 pt-3">
                        <div class="flex justify-between text-lg font-semibold">
                            <span>Total</span>
                            <span class="text-indigo-600">₹{{ $order->total_amount }}</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-3">
                        <p class="text-sm text-gray-600">
                            Payment Status: 
                            <span class="font-medium 
                                @if($order->payment_status === 'paid') text-green-600
                                @elseif($order->payment_status === 'pending') text-yellow-600
                                @else text-red-600 @endif">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 space-y-3">
                    <a href="{{ route('orders.track', $order) }}" 
                       class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200 text-center block">
                        Track Order
                    </a>
                    
                    @if($order->can_be_cancelled)
                        <button onclick="cancelOrder({{ $order->id }})" 
                                class="w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 transition duration-200">
                            Cancel Order
                        </button>
                    @endif
                    
                    <a href="{{ route('orders.index') }}" 
                       class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300 transition duration-200 text-center block">
                        Back to Orders
                    </a>
                </div>
            </div>

            <!-- Shipping & Billing Address -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Shipping Address</h2>
                @php
                    $shippingAddress = json_decode($order->shipping_address, true);
                @endphp
                <div class="text-sm text-gray-600">
                    <p class="font-medium">{{ $shippingAddress['name'] }}</p>
                    <p>{{ $shippingAddress['address'] }}</p>
                    <p>{{ $shippingAddress['city'] }}, {{ $shippingAddress['state'] }} - {{ $shippingAddress['pincode'] }}</p>
                    <p>{{ $shippingAddress['country'] }}</p>
                    <p class="mt-2">Phone: {{ $shippingAddress['phone'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function cancelOrder(orderId) {
    if (!confirm('Are you sure you want to cancel this order?')) {
        return;
    }

    fetch(`/orders/${orderId}/cancel`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while cancelling the order.');
    });
}
</script>
@endsection