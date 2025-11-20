@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 bg-indigo-100 rounded-lg">
                <i class="fas fa-box text-indigo-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Products</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $totalProducts }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
                <i class="fas fa-tags text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Categories</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $totalCategories }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Orders</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $totalOrders }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Recent Products</h3>
    </div>
    <div class="p-6">
        @if($recentProducts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($recentProducts as $product)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        @if($product->primaryImage)
                                            <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" 
                                                 alt="{{ $product->name }}"
                                                 class="w-10 h-10 rounded object-cover mr-3">
                                        @else
                                            <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center mr-3">
                                                <i class="fas fa-image text-gray-400"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $product->category->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">₹{{ $product->final_price }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $product->stock_quantity }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $product->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-box text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No products yet</h3>
                <p class="text-gray-500 mb-4">Get started by adding your first product</p>
                <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                    Add Product
                </a>
            </div>
        @endif
    </div>
</div>
@endsection