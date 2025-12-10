@extends('layouts.admin')

@section('title', 'Manage Orders')
@section('page-title', 'Orders Management')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
        <div class="text-sm text-gray-500">Total Orders</div>
    </div>
    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
       class="bg-yellow-50 p-4 rounded-lg shadow-sm border border-yellow-200 hover:shadow-md transition">
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
        <div class="text-sm text-yellow-700">Pending</div>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'confirmed']) }}"
       class="bg-blue-50 p-4 rounded-lg shadow-sm border border-blue-200 hover:shadow-md transition">
        <div class="text-2xl font-bold text-blue-600">{{ $stats['confirmed'] }}</div>
        <div class="text-sm text-blue-700">Confirmed</div>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}"
       class="bg-indigo-50 p-4 rounded-lg shadow-sm border border-indigo-200 hover:shadow-md transition">
        <div class="text-2xl font-bold text-indigo-600">{{ $stats['processing'] }}</div>
        <div class="text-sm text-indigo-700">Processing</div>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}"
       class="bg-purple-50 p-4 rounded-lg shadow-sm border border-purple-200 hover:shadow-md transition">
        <div class="text-2xl font-bold text-purple-600">{{ $stats['shipped'] }}</div>
        <div class="text-sm text-purple-700">Shipped</div>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}"
       class="bg-green-50 p-4 rounded-lg shadow-sm border border-green-200 hover:shadow-md transition">
        <div class="text-2xl font-bold text-green-600">{{ $stats['delivered'] }}</div>
        <div class="text-sm text-green-700">Delivered</div>
    </a>
    <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}"
       class="bg-red-50 p-4 rounded-lg shadow-sm border border-red-200 hover:shadow-md transition">
        <div class="text-2xl font-bold text-red-600">{{ $stats['cancelled'] }}</div>
        <div class="text-sm text-red-700">Cancelled</div>
    </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 p-4">
    <form action="{{ route('admin.orders.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Order number or customer name/email..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
        </div>
        <div class="w-40">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div class="w-40">
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment</label>
            <select name="payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">All Payments</option>
                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                <i class="fas fa-search mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.orders.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                <i class="fas fa-times mr-1"></i> Clear
            </a>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Items</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Shiprocket</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $order->order_number }}</div>
                            <div class="text-xs text-gray-500">#{{ $order->id }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $order->user->name ?? 'Guest' }}</div>
                            <div class="text-sm text-gray-500">{{ $order->user->email ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex -space-x-2">
                                @foreach($order->items->take(3) as $item)
                                    @if($item->product && $item->product->primaryImage)
                                        <img src="{{ asset('storage/' . $item->product->primaryImage->image_path) }}"
                                             alt="{{ $item->product_name }}"
                                             class="w-8 h-8 rounded-full border-2 border-white object-cover"
                                             title="{{ $item->product_name }}">
                                    @else
                                        <div class="w-8 h-8 rounded-full border-2 border-white bg-gray-200 flex items-center justify-center"
                                             title="{{ $item->product_name }}">
                                            <i class="fas fa-box text-gray-400 text-xs"></i>
                                        </div>
                                    @endif
                                @endforeach
                                @if($order->items->count() > 3)
                                    <div class="w-8 h-8 rounded-full border-2 border-white bg-gray-100 flex items-center justify-center text-xs text-gray-600">
                                        +{{ $order->items->count() - 3 }}
                                    </div>
                                @endif
                            </div>
                            <div class="text-xs text-gray-500 mt-1">{{ $order->items->count() }} item(s)</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">Rs. {{ number_format($order->total_amount, 2) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'confirmed' => 'bg-blue-100 text-blue-800',
                                    'processing' => 'bg-indigo-100 text-indigo-800',
                                    'shipped' => 'bg-purple-100 text-purple-800',
                                    'delivered' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $paymentColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'paid' => 'bg-green-100 text-green-800',
                                    'failed' => 'bg-red-100 text-red-800',
                                    'refunded' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $paymentColors[$order->payment_status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($order->shiprocket_order_id)
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs text-green-600 font-medium">
                                        <i class="fas fa-check-circle"></i> Synced
                                    </span>
                                    @if($order->awb_code)
                                        <span class="text-xs text-gray-600">AWB: {{ $order->awb_code }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400">
                                    <i class="fas fa-clock"></i> Not synced
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $order->created_at->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $order->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.orders.show', $order) }}"
                                   class="text-indigo-600 hover:text-indigo-900" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$order->shiprocket_order_id && in_array($order->status, ['confirmed', 'processing']))
                                    <form action="{{ route('admin.shiprocket.ship', $order) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Ship this order via Shiprocket?')">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900" title="Ship via Shiprocket">
                                            <i class="fas fa-shipping-fast"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">No orders found</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
