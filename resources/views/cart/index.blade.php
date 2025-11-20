@extends('layouts.app')

@section('title', 'Shopping Cart - Ecommerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Shopping Cart</h1>
        <p class="text-gray-600 mt-2">Review your items</p>
    </div>

    @if($cartItems->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                    <!-- Cart Header -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">
                            Your Cart ({{ $cartCount }} items)
                        </h2>
                    </div>

                    <!-- Cart Items List -->
                    <div class="divide-y divide-gray-200">
                        @foreach($cartItems as $cartItem)
                            <div class="p-6 flex items-center space-x-4">
                                <!-- Product Image -->
                                <div class="flex-shrink-0">
                                    @if($cartItem->product->primaryImage)
                                        <img src="{{ asset('storage/' . $cartItem->product->primaryImage->image_path) }}" 
                                             alt="{{ $cartItem->product->name }}"
                                             class="w-20 h-20 object-cover rounded-lg">
                                    @else
                                        <div class="w-20 h-20 bg-gray-200 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-image text-gray-400"></i>
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Details -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <a href="{{ route('products.show', $cartItem->product->slug) }}" class="hover:text-indigo-600">
                                            {{ $cartItem->product->name }}
                                        </a>
                                    </h3>
                                    
                                    @if($cartItem->variant)
                                        <p class="text-sm text-gray-600 mt-1">
                                            Variant: {{ $cartItem->variant->color_name }}
                                            @if($cartItem->variant->size)
                                                , Size: {{ $cartItem->variant->size }}
                                            @endif
                                        </p>
                                    @endif
                                    
                                    <p class="text-lg font-semibold text-indigo-600 mt-2">
                                        ₹<span class="item-total" data-cart-id="{{ $cartItem->id }}">
                                            {{ $cartItem->price * $cartItem->quantity }}
                                        </span>
                                    </p>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex items-center space-x-2">
                                    <button class="quantity-btn decrease bg-gray-200 rounded-full w-8 h-8 flex items-center justify-center hover:bg-gray-300"
                                            data-cart-id="{{ $cartItem->id }}"
                                            data-action="decrease">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    
                                    <span class="quantity-display w-12 text-center font-medium" data-cart-id="{{ $cartItem->id }}">
                                        {{ $cartItem->quantity }}
                                    </span>
                                    
                                    <button class="quantity-btn increase bg-gray-200 rounded-full w-8 h-8 flex items-center justify-center hover:bg-gray-300"
                                            data-cart-id="{{ $cartItem->id }}"
                                            data-action="increase">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>

                                <!-- Remove Button -->
                                <button class="remove-btn text-red-600 hover:text-red-800 p-2"
                                        data-cart-id="{{ $cartItem->id }}"
                                        title="Remove from cart">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Clear Cart Button -->
                <div class="mt-4">
                    <button id="clear-cart-btn" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700">
                        Clear Entire Cart
                    </button>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">₹<span id="cart-subtotal">{{ $cartTotal }}</span></span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium">₹0</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax</span>
                            <span class="font-medium">₹0</span>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-3">
                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total</span>
                                <span class="text-indigo-600">₹<span id="cart-total">{{ $cartTotal }}</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <a href="{{ route('checkout.index') }}" class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 font-semibold mt-6 text-center block">
    Proceed to Checkout
</a>

                    <!-- Continue Shopping -->
                    <a href="{{ route('products.index') }}" class="block text-center text-indigo-600 hover:text-indigo-700 mt-4">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="text-center py-12">
            <i class="fas fa-shopping-cart text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-600 mb-2">Your cart is empty</h3>
            <p class="text-gray-500 mb-6">Add some products to your cart to see them here</p>
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
        // Quantity controls
        document.querySelectorAll('.quantity-btn').forEach(button => {
            button.addEventListener('click', function() {
                const cartId = this.getAttribute('data-cart-id');
                const action = this.getAttribute('data-action');
                updateCartQuantity(cartId, action);
            });
        });

        // Remove item from cart
        document.querySelectorAll('.remove-btn').forEach(button => {
            button.addEventListener('click', function() {
                const cartId = this.getAttribute('data-cart-id');
                removeFromCart(cartId);
            });
        });

        // Clear entire cart
        document.getElementById('clear-cart-btn')?.addEventListener('click', function() {
            clearCart();
        });

        function updateCartQuantity(cartId, action) {
            const quantityDisplay = document.querySelector(`.quantity-display[data-cart-id="${cartId}"]`);
            let currentQuantity = parseInt(quantityDisplay.textContent);
            
            if (action === 'increase') {
                currentQuantity += 1;
            } else if (action === 'decrease' && currentQuantity > 1) {
                currentQuantity -= 1;
            } else {
                return; // Don't update if quantity would be 0
            }

            fetch(`/cart/update/${cartId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    quantity: currentQuantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update quantity display
                    quantityDisplay.textContent = currentQuantity;
                    
                    // Update item total
                    const itemTotal = document.querySelector(`.item-total[data-cart-id="${cartId}"]`);
                    itemTotal.textContent = data.item_total;
                    
                    // Update cart totals
                    document.getElementById('cart-subtotal').textContent = data.cart_total;
                    document.getElementById('cart-total').textContent = data.cart_total;
                    
                    // Update cart count in header
                    updateCartCount(data.cart_count);
                    
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
            });
        }

        function removeFromCart(cartId) {
            fetch(`/cart/remove/${cartId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove item from DOM
                    const cartItem = document.querySelector(`[data-cart-id="${cartId}"]`).closest('.flex.items-center');
                    cartItem.remove();
                    
                    // Update cart totals
                    document.getElementById('cart-subtotal').textContent = data.cart_total;
                    document.getElementById('cart-total').textContent = data.cart_total;
                    
                    // Update cart count in header
                    updateCartCount(data.cart_count);
                    
                    showNotification(data.message, 'success');
                    
                    // If cart is empty, reload page to show empty state
                    if (data.cart_count === 0) {
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

        function clearCart() {
            if (!confirm('Are you sure you want to clear your entire cart?')) {
                return;
            }

            fetch('/cart/clear', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    updateCartCount(data.cart_count);
                    
                    // Reload page to show empty state
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred', 'error');
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