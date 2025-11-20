@extends('layouts.app')

@section('title', $category->name . ' - Ecommerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Category Header -->
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }}</h1>
        @if($category->description)
            <p class="text-gray-600 mt-2 max-w-2xl mx-auto">{{ $category->description }}</p>
        @endif
    </div>

    <!-- Products Grid -->
    @if($products->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $products->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <i class="fas fa-folder-open text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">No products in this category</h3>
            <p class="text-gray-500">Check back later for new products</p>
            <a href="{{ route('products.index') }}" class="inline-block mt-4 bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700">
                Browse All Products
            </a>
        </div>
    @endif
</div>
@endsection