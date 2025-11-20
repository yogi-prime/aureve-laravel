@extends('layouts.app')

@section('title', 'Track Order - Ecommerce Store')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Track Your Order</h1>
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
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Tracking Timeline -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Tracking</h2>
                
                <!-- Timeline -->
                <div class="space-y-6">
                    @foreach($timeline as $step)
                        <div class="flex items-start space-x-4">
                            <!-- Timeline dot -->
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center
                                    @if($step['completed']) bg-green-500
                                    @elseif($step['active']) bg-indigo-500
                                    @else bg-gray-300 @endif">
                                    @if($step['completed'])
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    @elseif($step['active'])
                                        <div class="w-2 h-2 bg-white rounded-full"></div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Timeline content -->
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-medium text-gray-900">{{ $step['event'] }}</h3>
                                <p class="text-gray-600 mt-1">{{ $step['description'] }}</p>
                                @if($step['date'])
                                    <p class="text-sm text-gray-500 mt-2">
                                        {{ $step['date']->format('M d, Y \\a\\t h:i A') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <!-- Connector line (except for last item) -->
                        @if(!$loop->last)
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-6 flex justify-center">
                                        <div class="w-0.5 h-full bg-gray-300"></div>
                                    </div>
                                </div>
                                <div class="flex-1"></div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Static Tracking Info (ShipRocket integration ke liye ready) -->
                <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                    <h3 class="text-lg font-medium text-blue-900 mb-2">Shipping Information</h3>
                    <p class="text-blue-700">
                        @if($order->status === 'shipped' && $order->tracking_number)
                            Tracking Number: <strong>{{ $order->tracking_number }}</strong>
                        @else
                            Your order is being processed. Tracking information will be available once shipped.
                        @endif
                    </p>
                    <p class="text-sm text-blue-600 mt-2">
                        For detailed tracking, you'll be able to use ShipRocket integration soon.
                    </p>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>
                
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Total</span>
                        <span class="font-semibold">₹{{ $order->total_amount }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Items</span>
                        <span class="font-medium">{{ $order->items->count() }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Date</span>
                        <span class="font-medium">{{ $order->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                <!-- Order Items Preview -->
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <h3 class="font-medium text-gray-900 mb-3">Items in this order</h3>
                    <div class="space-y-3">
                        @foreach($order->items->take(3) as $item)
                            <div class="flex items-center space-x-3">
                                @if($item->product->primaryImage)
                                    <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}" 
                                         alt="{{ $item->product->name }}"
                                         class="w-10 h-10 object-cover rounded">
                                @else
                                    <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $item->product_name }}
                                    </p>
                                    <p class="text-xs text-gray-500">Qty: {{ $item->quantity }}</p>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($order->items->count() > 3)
                            <p class="text-sm text-gray-500 text-center">
                                +{{ $order->items->count() - 3 }} more items
                            </p>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 space-y-3">
                    <a href="{{ route('orders.show', $order) }}" 
                       class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200 text-center block">
                        View Order Details
                    </a>
                    
                    <a href="{{ route('orders.index') }}" 
                       class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300 transition duration-200 text-center block">
                        Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection