@extends('layouts.app')

@section('title', 'All Products - Ecommerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">All Products</h1>
        <p class="text-gray-600 mt-2">Discover our amazing collection of products</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <div class="lg:w-1/4">
            <div class="bg-white p-6 rounded-lg shadow-md">
                <h3 class="font-bold text-lg mb-4">Filters</h3>
                
                <!-- Categories -->
                <div class="mb-6">
                    <h4 class="font-semibold mb-3">Categories</h4>
                    <div class="space-y-2">
                        @foreach($categories as $category)
                            <label class="flex items-center">
                                <input type="radio" name="category" value="{{ $category->id }}" 
                                    {{ request('category') == $category->id ? 'checked' : '' }}
                                    class="category-filter mr-2">
                                <span class="text-sm">{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Price Range -->
                <div class="mb-6">
                    <h4 class="font-semibold mb-3">Price Range</h4>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2">
                            <input type="number" id="min_price" placeholder="Min" 
                                value="{{ request('min_price') }}"
                                class="w-24 px-2 py-1 border border-gray-300 rounded text-sm">
                            <span>-</span>
                            <input type="number" id="max_price" placeholder="Max" 
                                value="{{ request('max_price') }}"
                                class="w-24 px-2 py-1 border border-gray-300 rounded text-sm">
                        </div>
                    </div>
                </div>

                <!-- Clear Filters -->
                <button onclick="clearFilters()" class="w-full bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 text-sm">
                    Clear All Filters
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="lg:w-3/4">
            <!-- Sort and Search -->
            <div class="bg-white p-4 rounded-lg shadow-md mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
                    <div class="text-sm text-gray-600">
                        Showing {{ $products->firstItem() }} - {{ $products->lastItem() }} of {{ $products->total() }} products
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <select id="sort-select" class="border border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                        </select>
                    </div>
                </div>
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
                    <i class="fas fa-search text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No products found</h3>
                    <p class="text-gray-500">Try adjusting your search or filters</p>
                </div>
            @endif
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const categoryFilters = document.querySelectorAll('.category-filter');
        const sortSelect = document.getElementById('sort-select');
        const minPriceInput = document.getElementById('min_price');
        const maxPriceInput = document.getElementById('max_price');

        function applyFilters() {
            const params = new URLSearchParams(window.location.search);
            
            // Category
            const selectedCategory = document.querySelector('.category-filter:checked');
            if (selectedCategory) {
                params.set('category', selectedCategory.value);
            } else {
                params.delete('category');
            }

            // Price
            if (minPriceInput.value) params.set('min_price', minPriceInput.value);
            else params.delete('min_price');
            
            if (maxPriceInput.value) params.set('max_price', maxPriceInput.value);
            else params.delete('max_price');

            // Sort
            if (sortSelect.value !== 'latest') params.set('sort', sortSelect.value);
            else params.delete('sort');

            window.location.href = '{{ route('products.index') }}?' + params.toString();
        }

        categoryFilters.forEach(filter => {
            filter.addEventListener('change', applyFilters);
        });

        sortSelect.addEventListener('change', applyFilters);

        // Price filter with debounce
        let priceTimeout;
        [minPriceInput, maxPriceInput].forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(priceTimeout);
                priceTimeout = setTimeout(applyFilters, 1000);
            });
        });
    });

    function clearFilters() {
        window.location.href = '{{ route('products.index') }}';
    }
</script>
@endsection
@endsection