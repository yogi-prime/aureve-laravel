@extends('layouts.app')

@section('title', 'Welcome to Ecommerce Store')

@section('content')
<!-- Hero Section -->
<section class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">Welcome to Our Store</h1>
        <p class="text-xl mb-8 max-w-2xl mx-auto">Discover amazing products at great prices. Shop with confidence and style.</p>
        <a href="{{ route('products.index') }}" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold text-lg hover:bg-gray-100 transition-colors">
            Shop Now
        </a>
    </div>
</section>

<!-- Featured Products -->
@if($featuredProducts->count() > 0)
<section class="max-w-7xl mx-auto px-4 py-12">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Products</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">Handpicked products just for you</p>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($featuredProducts as $product)
            <x-product-card :product="$product" />
        @endforeach
    </div>
    
    <div class="text-center mt-8">
        <a href="{{ route('products.featured') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">
            View All Featured Products →
        </a>
    </div>
</section>
@endif

<!-- New Arrivals -->
@if($newArrivals->count() > 0)
<section class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">New Arrivals</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Check out our latest products</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($newArrivals as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
        
        <div class="text-center mt-8">
            <a href="{{ route('products.new-arrivals') }}" class="text-indigo-600 hover:text-indigo-700 font-semibold">
                View All New Arrivals →
            </a>
        </div>
    </div>
</section>
@endif

<!-- Features -->
<section class="max-w-7xl mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="text-center">
            <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shipping-fast text-indigo-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2">Free Shipping</h3>
            <p class="text-gray-600">Free shipping on orders over ₹999</p>
        </div>
        
        <div class="text-center">
            <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-shield-alt text-indigo-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2">Secure Payment</h3>
            <p class="text-gray-600">Your payment information is safe with us</p>
        </div>
        
        <div class="text-center">
            <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-undo-alt text-indigo-600 text-2xl"></i>
            </div>
            <h3 class="text-xl font-semibold mb-2">Easy Returns</h3>
            <p class="text-gray-600">30-day return policy on all items</p>
        </div>
    </div>
</section>
@endsection