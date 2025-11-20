@extends('layouts.app')

@section('title', 'Welcome - Ecommerce Store')

@section('content')
<!-- Hero Section -->
<div class="bg-white">
    <div class="max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl md:text-6xl">
                <span class="block">Welcome to</span>
                <span class="block text-indigo-600">Ecommerce Store</span>
            </h1>
            <p class="mt-3 max-w-md mx-auto text-base text-gray-500 sm:text-lg md:mt-5 md:text-xl md:max-w-3xl">
                Discover amazing products at great prices. Shop with confidence and enjoy fast delivery.
            </p>
            <div class="mt-5 max-w-md mx-auto sm:flex sm:justify-center md:mt-8">
                <div class="rounded-md shadow">
                    <a href="{{ route('products.index') }}" 
                       class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10">
                        Shop Now
                    </a>
                </div>
                <div class="mt-3 rounded-md shadow sm:mt-0 sm:ml-3">
                    <a href="{{ route('products.featured') }}" 
                       class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-600 bg-white hover:bg-gray-50 md:py-4 md:text-lg md:px-10">
                        Featured
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="bg-gray-50">
    <div class="max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:py-24 lg:px-8">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl font-extrabold text-gray-900">Why Shop With Us?</h2>
            <p class="mt-4 text-lg text-gray-500">We provide the best shopping experience for our customers.</p>
        </div>
        <div class="mt-12 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3">
            <div class="pt-6">
                <div class="flow-root bg-white rounded-lg px-6 pb-8">
                    <div class="-mt-6">
                        <div class="inline-flex items-center justify-center p-3 bg-indigo-500 rounded-md shadow-lg">
                            <i class="fas fa-shipping-fast text-white text-xl"></i>
                        </div>
                        <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Free Shipping</h3>
                        <p class="mt-5 text-base text-gray-500">Free shipping on all orders over $50. Fast and reliable delivery.</p>
                    </div>
                </div>
            </div>

            <div class="pt-6">
                <div class="flow-root bg-white rounded-lg px-6 pb-8">
                    <div class="-mt-6">
                        <div class="inline-flex items-center justify-center p-3 bg-indigo-500 rounded-md shadow-lg">
                            <i class="fas fa-shield-alt text-white text-xl"></i>
                        </div>
                        <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">Secure Payment</h3>
                        <p class="mt-5 text-base text-gray-500">Your payments are secure with our encrypted payment system.</p>
                    </div>
                </div>
            </div>

            <div class="pt-6">
                <div class="flow-root bg-white rounded-lg px-6 pb-8">
                    <div class="-mt-6">
                        <div class="inline-flex items-center justify-center p-3 bg-indigo-500 rounded-md shadow-lg">
                            <i class="fas fa-headset text-white text-xl"></i>
                        </div>
                        <h3 class="mt-8 text-lg font-medium text-gray-900 tracking-tight">24/7 Support</h3>
                        <p class="mt-5 text-base text-gray-500">Get help anytime with our 24/7 customer support team.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection