@extends('layouts.app')

@section('title', $product->name . ' - Ecommerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ route('home') }}" class="text-gray-500 hover:text-indigo-600">Home</a></li>
            <li><i class="fas fa-chevron-right text-gray-400 text-xs"></i></li>
            <li><a href="{{ route('products.category', $product->category->slug) }}" class="text-gray-500 hover:text-indigo-600">{{ $product->category->name }}</a></li>
            <li><i class="fas fa-chevron-right text-gray-400 text-xs"></i></li>
            <li class="text-gray-900">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 p-8">
            <!-- Product Images -->
            <div>
                <!-- Main Image -->
                <div class="mb-4">
                    @if($product->images->count() > 0)
                        <img id="main-image" src="{{ asset('storage/' . $product->images->first()->image_path) }}" 
                             alt="{{ $product->images->first()->alt_text ?? $product->name }}"
                             class="w-full h-96 object-cover rounded-lg">
                    @else
                        <div class="w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-6xl"></i>
                        </div>
                    @endif
                </div>

                <!-- Thumbnail Images -->
                @if($product->images->count() > 1)
                    <div class="flex space-x-2 overflow-x-auto">
                        @foreach($product->images as $image)
                            <img src="{{ asset('storage/' . $image->image_path) }}" 
                                 alt="{{ $image->alt_text ?? $product->name }}"
                                 class="w-20 h-20 object-cover rounded border-2 cursor-pointer thumbnail-image
                                        {{ $loop->first ? 'border-indigo-600' : 'border-transparent' }}"
                                 onclick="changeMainImage('{{ asset('storage/' . $image->image_path) }}', this)">
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Product Details -->
            <div>
                <!-- Category & Brand -->
                <div class="flex items-center space-x-4 text-sm text-gray-500 mb-2">
                    <span>{{ $product->category->name }}</span>
                    @if($product->brand)
                        <span>•</span>
                        <span>{{ $product->brand }}</span>
                    @endif
                </div>

                <!-- Product Name -->
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $product->name }}</h1>

                <!-- Price -->
                <div class="flex items-center space-x-3 mb-4">
                    @if($product->is_on_sale)
                        <span class="text-3xl font-bold text-gray-900">₹{{ $product->final_price }}</span>
                        <span class="text-xl text-gray-500 line-through">₹{{ $product->base_price }}</span>
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm font-bold">
                            Save {{ $product->discount_percentage }}%
                        </span>
                    @else
                        <span class="text-3xl font-bold text-gray-900">₹{{ $product->final_price }}</span>
                    @endif
                </div>

                <!-- Stock Status -->
                <div class="mb-6">
                    <span class="{{ $product->in_stock ? 'text-green-600' : 'text-red-600' }} font-semibold">
                        <i class="fas {{ $product->in_stock ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                        {{ $product->in_stock ? 'In Stock' : 'Out of Stock' }}
                    </span>
                    @if($product->in_stock && $product->stock_quantity > 0)
                        <span class="text-gray-500 text-sm ml-2">({{ $product->stock_quantity }} available)</span>
                    @endif
                </div>

                <!-- Variants -->
                @if($product->variants->count() > 0)
                    <div class="mb-6">
                        <h3 class="font-semibold mb-3">Available Variants:</h3>
                        <div class="grid grid-cols-1 gap-3" id="variants-container">
                            @foreach($product->variants as $variant)
                                <div class="border border-gray-300 rounded-lg p-3 hover:border-indigo-600 cursor-pointer variant-option 
                                    {{ $loop->first ? 'border-indigo-600 bg-indigo-50' : '' }}"
                                    data-variant-id="{{ $variant->id }}"
                                    data-variant-price="{{ $variant->final_price }}"
                                    data-variant-stock="{{ $variant->stock_quantity }}"
                                    onclick="selectVariant(this)">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-medium">{{ $variant->color_name }}</div>
                                            @if($variant->size)
                                                <div class="text-sm text-gray-600">Size: {{ $variant->size }}</div>
                                            @endif
                                            @if($variant->material)
                                                <div class="text-sm text-gray-600">Material: {{ $variant->material }}</div>
                                            @endif
                                            <div class="text-lg font-bold text-indigo-600 variant-price">₹{{ $variant->final_price }}</div>
                                        </div>
                                        @if($variant->color_code)
                                            <div class="w-8 h-8 rounded-full border border-gray-300" 
                                                 style="background-color: {{ $variant->color_code }}"
                                                 title="{{ $variant->color_name }}"></div>
                                        @endif
                                    </div>
                                    <div class="text-sm {{ $variant->stock_quantity > 0 ? 'text-green-600' : 'text-red-600' }} mt-1 variant-stock">
                                        {{ $variant->stock_quantity > 0 ? 'In Stock (' . $variant->stock_quantity . ' available)' : 'Out of Stock' }}
                                    </div>
                                    @if($variant->variant_description)
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $variant->variant_description }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Quantity Selector -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                    <div class="flex items-center space-x-3">
                        <button class="quantity-btn decrease bg-gray-200 rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-300"
                                onclick="updateQuantity(-1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" 
                               class="w-20 text-center border border-gray-300 rounded-lg py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500">
                        <button class="quantity-btn increase bg-gray-200 rounded-full w-10 h-10 flex items-center justify-center hover:bg-gray-300"
                                onclick="updateQuantity(1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4 mb-6">
                    <button class="flex-1 bg-indigo-600 text-white py-3 px-6 rounded-lg hover:bg-indigo-700 font-semibold text-lg add-to-cart-btn"
                            data-product-id="{{ $product->id }}"
                            {{ !$product->in_stock ? 'disabled' : '' }}>
                        {{ $product->in_stock ? 'Add to Cart' : 'Out of Stock' }}
                    </button>
                    <button class="w-12 h-12 border border-gray-300 rounded-lg hover:border-indigo-600 flex items-center justify-center wishlist-btn"
                            data-product-id="{{ $product->id }}"
                            title="Add to Wishlist">
                        <i class="far fa-heart text-xl"></i>
                    </button>
                </div>

                <!-- Additional Info -->
                <div class="border-t border-gray-200 pt-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">SKU:</span>
                            <span class="font-medium">{{ $product->sku }}</span>
                        </div>
                        @if($product->brand)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Brand:</span>
                            <span class="font-medium">{{ $product->brand }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-600">Views:</span>
                            <span class="font-medium">{{ $product->view_count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Tabs -->
        <div class="border-t border-gray-200">
            <div class="px-8">
                <div class="flex border-b border-gray-200">
                    <button class="tab-button py-4 px-6 border-b-2 border-indigo-600 text-indigo-600 font-semibold" data-tab="description">
                        Description
                    </button>
                    <button class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-semibold" data-tab="specifications">
                        Specifications
                    </button>
                    <button class="tab-button py-4 px-6 border-b-2 border-transparent text-gray-500 hover:text-gray-700 font-semibold" data-tab="reviews">
                        Reviews
                    </button>
                </div>

                <div class="py-6">
                    <!-- Description Tab -->
                    <div id="description-tab" class="tab-content">
                        <div class="prose max-w-none">
                            {!! $product->description !!}
                        </div>
                    </div>

                    <!-- Specifications Tab -->
                    <div id="specifications-tab" class="tab-content hidden">
                        @if($product->specifications)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($product->specifications as $key => $value)
                                    <div class="flex justify-between border-b border-gray-100 py-2">
                                        <span class="font-medium text-gray-600">{{ ucfirst($key) }}:</span>
                                        <span class="text-gray-900">{{ $value }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No specifications available.</p>
                        @endif
                    </div>

                    <!-- Reviews Tab -->
                    <div id="reviews-tab" class="tab-content hidden">
                        <p class="text-gray-500">Reviews will be implemented later.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Products</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $relatedProduct)
                    <x-product-card :product="$relatedProduct" />
                @endforeach
            </div>
        </div>
    @endif
</div>

@section('scripts')
<script>
    let selectedVariantId = null;
    let currentPrice = {{ $product->final_price }};
    let currentStock = {{ $product->stock_quantity }};

    function changeMainImage(src, element) {
        document.getElementById('main-image').src = src;
        document.querySelectorAll('.thumbnail-image').forEach(img => {
            img.classList.remove('border-indigo-600');
            img.classList.add('border-transparent');
        });
        element.classList.add('border-indigo-600');
        element.classList.remove('border-transparent');
    }

    // Tab functionality
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tab = this.getAttribute('data-tab');
            
            // Update active tab button
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('border-indigo-600', 'text-indigo-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            });
            this.classList.add('border-indigo-600', 'text-indigo-600');
            this.classList.remove('border-transparent', 'text-gray-500');
            
            // Show active tab content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.getElementById(tab + '-tab').classList.remove('hidden');
        });
    });

    // Variant selection
    function selectVariant(element) {
        // Remove selection from all variants
        document.querySelectorAll('.variant-option').forEach(variant => {
            variant.classList.remove('border-indigo-600', 'bg-indigo-50');
            variant.classList.add('border-gray-300');
        });
        
        // Add selection to clicked variant
        element.classList.add('border-indigo-600', 'bg-indigo-50');
        element.classList.remove('border-gray-300');
        
        // Update selected variant
        selectedVariantId = element.getAttribute('data-variant-id');
        currentPrice = parseFloat(element.getAttribute('data-variant-price'));
        currentStock = parseInt(element.getAttribute('data-variant-stock'));
        
        // Update main price display (you can add this if you want to show price change)
        console.log('Selected variant:', selectedVariantId, 'Price:', currentPrice, 'Stock:', currentStock);
        
        // Update add to cart button based on stock
        const addToCartBtn = document.querySelector('.add-to-cart-btn');
        if (currentStock > 0) {
            addToCartBtn.disabled = false;
            addToCartBtn.textContent = 'Add to Cart';
        } else {
            addToCartBtn.disabled = true;
            addToCartBtn.textContent = 'Out of Stock';
        }
    }

    // Quantity update
    function updateQuantity(change) {
        const quantityInput = document.getElementById('quantity');
        let currentQuantity = parseInt(quantityInput.value);
        let newQuantity = currentQuantity + change;
        
        // Ensure quantity doesn't go below 1
        if (newQuantity < 1) {
            newQuantity = 1;
        }
        
        // Ensure quantity doesn't exceed available stock
        const maxStock = selectedVariantId ? currentStock : {{ $product->stock_quantity }};
        if (newQuantity > maxStock) {
            newQuantity = maxStock;
            showNotification(`Only ${maxStock} items available in stock`, 'warning');
        }
        
        quantityInput.value = newQuantity;
    }

    // Add to cart functionality
    document.querySelector('.add-to-cart-btn').addEventListener('click', function() {
        const productId = this.getAttribute('data-product-id');
        const quantity = document.getElementById('quantity').value;
        
        addToCart(productId, selectedVariantId, quantity);
    });

    // Wishlist functionality
    document.querySelector('.wishlist-btn').addEventListener('click', function() {
        const productId = this.getAttribute('data-product-id');
        const button = this;
        
        addToWishlist(productId, button);
    });

   function addToCart(productId, variantId, quantity) {
    // Check if user is logged in
    @if(!Auth::check())
        showNotification('Please login to add items to cart', 'error');
        setTimeout(() => {
            window.location.href = '{{ route("login") }}';
        }, 2000);
        return;
    @endif

    const payload = {
        product_id: productId,
        quantity: parseInt(quantity)
    };
    
    if (variantId) {
        payload.variant_id = variantId;
    }

    fetch('{{ route("cart.add") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (response.status === 401) {
            // Unauthorized - user not logged in
            showNotification('Please login to add items to cart', 'error');
            setTimeout(() => {
                window.location.href = '{{ route("login") }}';
            }, 2000);
            return;
        }
        return response.json();
    })
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
        showNotification('An error occurred while adding to cart', 'error');
    });
}

    function addToWishlist(productId, button) {
        fetch('{{ route("wishlist.add") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                product_id: productId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message, 'success');
                updateWishlistCount(data.wishlist_count);
                
                // Update heart icon
                const heartIcon = button.querySelector('i');
                heartIcon.className = 'fas fa-heart text-red-500';
                
                // Change button behavior
                button.onclick = function() {
                    removeFromWishlist(productId, button);
                };
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
            showNotification('An error occurred while adding to wishlist', 'error');
        });
    }

    function removeFromWishlist(productId, button) {
        fetch(`/wishlist/remove-product/${productId}`, {
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
                
                // Update heart icon
                const heartIcon = button.querySelector('i');
                heartIcon.className = 'far fa-heart text-xl';
                
                // Change button behavior back
                button.onclick = function() {
                    addToWishlist(productId, button);
                };
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while removing from wishlist', 'error');
        });
    }

    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('#cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
        });
    }

    function updateWishlistCount(count) {
        const wishlistCountElements = document.querySelectorAll('#wishlist-count');
        wishlistCountElements.forEach(element => {
            element.textContent = count;
        });
    }

    function showNotification(message, type) {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-yellow-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove notification after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Check initial wishlist status
    document.addEventListener('DOMContentLoaded', function() {
        const productId = document.querySelector('.wishlist-btn').getAttribute('data-product-id');
        const wishlistButton = document.querySelector('.wishlist-btn');
        
        checkWishlistStatus(productId, wishlistButton);
    });

    function checkWishlistStatus(productId, button) {
        fetch(`/wishlist/check/${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.in_wishlist) {
                    const heartIcon = button.querySelector('i');
                    heartIcon.className = 'fas fa-heart text-red-500';
                    
                    // Change button behavior to remove
                    button.onclick = function() {
                        removeFromWishlist(productId, button);
                    };
                }
            })
            .catch(error => {
                console.error('Error checking wishlist status:', error);
            });
    }

    // Prevent quantity input from going below 1 or above stock
    document.getElementById('quantity').addEventListener('change', function() {
        let quantity = parseInt(this.value);
        const maxStock = selectedVariantId ? currentStock : {{ $product->stock_quantity }};
        
        if (isNaN(quantity) || quantity < 1) {
            this.value = 1;
        } else if (quantity > maxStock) {
            this.value = maxStock;
            showNotification(`Only ${maxStock} items available in stock`, 'warning');
        }
    });
</script>
@endsection
@endsection