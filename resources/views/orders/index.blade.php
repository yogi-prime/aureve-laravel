@extends('layouts.app')

@section('title', 'My Orders - Ecommerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
        <p class="text-gray-600 mt-2">View your order history and track shipments</p>
    </div>

    @if($orders->count() > 0)
        <!-- Orders List -->
        <div class="space-y-6">
            @foreach($orders as $order)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <!-- Order Header -->
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">
                                Order #{{ $order->order_number }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Placed on {{ $order->created_at->format('M d, Y \\a\\t h:i A') }}
                            </p>
                        </div>
                        <div class="mt-2 md:mt-0">
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

                    <!-- Order Items -->
                    <div class="border-t border-gray-200 pt-4">
                        @foreach($order->items->take(2) as $item)
                            <div class="flex items-center space-x-3 mb-3">
                                @if($item->product->primaryImage)
                                    <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" 
                                         alt="{{ $item->product->name }}"
                                         class="w-12 h-12 object-cover rounded">
                                @else
                                    <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $item->product_name }}
                                    </p>
                                    @if($item->variant_attributes)
                                        <p class="text-xs text-gray-500">{{ $item->variant_attributes }}</p>
                                    @endif
                                    <p class="text-xs text-gray-500">Qty: {{ $item->quantity }} × ₹{{ $item->price }}</p>
                                </div>
                            </div>
                        @endforeach

                        @if($order->items->count() > 2)
                            <p class="text-sm text-gray-500 mt-2">
                                +{{ $order->items->count() - 2 }} more items
                            </p>
                        @endif
                    </div>

                    <!-- Order Footer -->
                    <div class="border-t border-gray-200 pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="mb-3 sm:mb-0">
                            <p class="text-lg font-semibold text-gray-900">
                                Total: ₹{{ $order->total_amount }}
                            </p>
                            <p class="text-sm text-gray-500">
                                Payment: 
                                <span class="font-medium 
                                    @if($order->payment_status === 'paid') text-green-600
                                    @elseif($order->payment_status === 'pending') text-yellow-600
                                    @else text-red-600 @endif">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </p>
                        </div>
                        <div class="flex space-x-3">
                            <a href="{{ route('orders.show', $order) }}" 
                               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                View Details
                            </a>
                            <a href="{{ route('orders.track', $order) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                                Track Order
                            </a>
                            @if($order->can_be_cancelled)
                                <button onclick="cancelOrder({{ $order->id }})" 
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                    Cancel Order
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No orders yet</h3>
            <p class="mt-2 text-gray-500">You haven't placed any orders yet.</p>
            <div class="mt-6">
                <a href="{{ route('products.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    Start Shopping
                </a>
            </div>
        </div>
    @endif
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