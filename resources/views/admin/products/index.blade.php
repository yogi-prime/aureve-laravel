@extends('layouts.admin')

@section('title', 'Manage Products')
@section('page-title', 'Products Management')

@section('header-actions')
    <a href="{{ route('admin.products.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>Add Product
    </a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Products Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Variants</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SEO Score</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($products as $product)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($product->primaryImage)
                                    <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" 
                                         alt="{{ $product->name }}"
                                         class="w-12 h-12 rounded object-cover mr-3">
                                @else
                                    <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center mr-3">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $product->category->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <div>₹{{ $product->final_price }}</div>
                            @if($product->is_on_sale)
                                <div class="text-xs text-green-600">On Sale</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $product->stock_quantity }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                {{ $product->variants->count() }} variants
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $seoScore = app('App\Http\Controllers\Admin\ProductController')->calculateSeoScore($product);
                            @endphp
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-green-600 h-2 rounded-full" 
                                         style="width: {{ $seoScore['percentage'] }}%"></div>
                                </div>
                                <span class="text-sm font-medium {{ $seoScore['percentage'] >= 80 ? 'text-green-600' : ($seoScore['percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $seoScore['percentage'] }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col space-y-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                                @if($product->is_featured)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Featured
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.products.seo-analysis', $product) }}" 
                                   class="text-green-600 hover:text-green-900" title="SEO Analysis">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                                <a href="{{ route('products.show', $product->slug) }}" 
                                   target="_blank" class="text-blue-600 hover:text-blue-900" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.products.edit', $product) }}" 
                                   class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to delete this product?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $products->links() }}
        </div>
    @endif
</div>
@endsection