@extends('layouts.app')

@section('title', 'My Wishlist - Ecommerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">My Wishlist</h1>
        <p class="text-gray-600 mt-2">Your favorite products</p>
    </div>

    @if($wishlistItems->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($wishlistItems as $wishlistItem)
                @php
                    $product = $wishlistItem->product;
                @endphp
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <!-- Product Image -->
                    <div class="relative">
                        <a href="{{ route('products.show', $product->slug) }}">
                            @if($product->primaryImage)
                                <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}" 
                                     alt="{{ $product->primaryImage->alt_text ?? $product->name }}"
                                     class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-4xl"></i>
                                </div>
                            @endif
                        </a>
                        
                        <!-- Remove from Wishlist Button -->
                        <button class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-md hover:bg-gray-100 remove-wishlist-btn"
                                data-product-id="{{ $product->id }}"
                                data-wishlist-id="{{ $wishlistItem->id }}"
                                title="Remove from Wishlist">
                            <i class="fas fa-heart text-red-500"></i>
                        </button>
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <!-- Category -->
                        <div class="text-xs text-gray-500 mb-1">
                            {{ $product->category->name }}
                        </div>
                        
                        <!-- Product Name -->
                        <h3 class="font-semibold text-lg mb-2">
                            <a href="{{ route('products.show', $product->slug) }}" class="hover:text-indigo-600">
                                {{ $product->name }}
                            </a>
                        </h3>
                        
                        <!-- Price -->
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2">
                                @if($product->is_on_sale)
                                    <span class="text-lg font-bold text-gray-900">₹{{ $product->final_price }}</span>
                                    <span class="text-sm text-gray-500 line-through">₹{{ $product->base_price }}</span>
                                @else
                                    <span class="text-lg font-bold text-gray-900">₹{{ $product->final_price }}</span>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Add to Cart Button -->
                        <button class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium add-to-cart-btn"
                                data-product-id="{{ $product->id }}"
                                {{ !$product->in_stock ? 'disabled' : '' }}>
                            {{ $product->in_stock ? 'Add to Cart' : 'Out of Stock' }}
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty Wishlist -->
        <div class="text-center py-12">
            <i class="far fa-heart text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Your wishlist is empty</h3>
            <p class="text-gray-500 mb-6">Add some products to your wishlist to see them here</p>
            <a href="{{ route('products.index') }}" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 font-semibold">
                Start Shopping
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Remove from wishlist
        document.querySelectorAll('.remove-wishlist-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                const wishlistId = this.getAttribute('data-wishlist-id');
                removeFromWishlist(productId, wishlistId, this);
            });
        });

        // Add to cart from wishlist
        document.querySelectorAll('.add-to-cart-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-product-id');
                addToCart(productId);
            });
        });

        function removeFromWishlist(productId, wishlistId, button) {
            fetch(`/wishlist/remove/${wishlistId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    updateWishlistCount(data.wishlist_count);
                    
                    // Remove product card from DOM
                    button.closest('.bg-white').remove();
                    
                    // If wishlist is empty, reload page to show empty state
                    if (data.wishlist_count === 0) {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            });
        }

        function addToCart(productId) {
            fetch('{{ route("cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    updateCartCount(data.cart_count);
                } else {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        showNotification(data.message, 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            });
        }

        function updateWishlistCount(count) {
            const wishlistCountElements = document.querySelectorAll('#wishlist-count');
            wishlistCountElements.forEach(element => {
                element.textContent = count;
            });
        }

        function updateCartCount(count) {
            const cartCountElements = document.querySelectorAll('#cart-count');
            cartCountElements.forEach(element => {
                element.textContent = count;
            });
        }

        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    });
</script>
@endpush