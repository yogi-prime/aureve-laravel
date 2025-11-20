@extends('layouts.app')

@section('title', 'Checkout - Ecommerce Store')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Checkout</h1>
        <p class="text-gray-600 mt-2">Complete your purchase</p>
    </div>

    <form id="checkout-form" method="POST" action="{{ route('checkout.process') }}">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Shipping Address -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Shipping Address</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="shipping_name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                            <input type="text" id="shipping_name" name="shipping_name" required
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('shipping_name', $user->name) }}">
                        </div>
                        
                        <div>
                            <label for="shipping_phone" class="block text-sm font-medium text-gray-700">Phone Number *</label>
                            <input type="text" id="shipping_phone" name="shipping_phone" required
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('shipping_phone', $user->phone) }}">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="shipping_address" class="block text-sm font-medium text-gray-700">Address *</label>
                            <textarea id="shipping_address" name="shipping_address" rows="3" required
                                      class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('shipping_address', $user->address) }}</textarea>
                        </div>
                        
                        <div>
                            <label for="shipping_city" class="block text-sm font-medium text-gray-700">City *</label>
                            <input type="text" id="shipping_city" name="shipping_city" required
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('shipping_city', $user->city) }}">
                        </div>
                        
                        <div>
                            <label for="shipping_state" class="block text-sm font-medium text-gray-700">State *</label>
                            <input type="text" id="shipping_state" name="shipping_state" required
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('shipping_state', $user->state) }}">
                        </div>
                        
                        <div>
                            <label for="shipping_country" class="block text-sm font-medium text-gray-700">Country *</label>
                            <input type="text" id="shipping_country" name="shipping_country" required
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('shipping_country', $user->country) }}">
                        </div>
                        
                        <div>
                            <label for="shipping_pincode" class="block text-sm font-medium text-gray-700">Pincode *</label>
                            <input type="text" id="shipping_pincode" name="shipping_pincode" required
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   value="{{ old('shipping_pincode', $user->pincode) }}">
                        </div>
                    </div>
                </div>

                <!-- Billing Address -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Billing Address</h2>
                        <label class="flex items-center">
                            <input type="checkbox" id="billing_same_as_shipping" name="billing_same_as_shipping" value="1" checked
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Same as shipping address</span>
                        </label>
                    </div>
                    
                    <div id="billing-address-fields" class="hidden">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="billing_name" class="block text-sm font-medium text-gray-700">Full Name *</label>
                                <input type="text" id="billing_name" name="billing_name"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ old('billing_name', $user->name) }}">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="billing_address" class="block text-sm font-medium text-gray-700">Address *</label>
                                <textarea id="billing_address" name="billing_address" rows="3"
                                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('billing_address', $user->address) }}</textarea>
                            </div>
                            
                            <div>
                                <label for="billing_city" class="block text-sm font-medium text-gray-700">City *</label>
                                <input type="text" id="billing_city" name="billing_city"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ old('billing_city', $user->city) }}">
                            </div>
                            
                            <div>
                                <label for="billing_state" class="block text-sm font-medium text-gray-700">State *</label>
                                <input type="text" id="billing_state" name="billing_state"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ old('billing_state', $user->state) }}">
                            </div>
                            
                            <div>
                                <label for="billing_country" class="block text-sm font-medium text-gray-700">Country *</label>
                                <input type="text" id="billing_country" name="billing_country"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ old('billing_country', $user->country) }}">
                            </div>
                            
                            <div>
                                <label for="billing_pincode" class="block text-sm font-medium text-gray-700">Pincode *</label>
                                <input type="text" id="billing_pincode" name="billing_pincode"
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                       value="{{ old('billing_pincode', $user->pincode) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Method</h2>
                    
                    <div class="space-y-4">
                        <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500">
                            <input type="radio" name="payment_method" value="razorpay" class="text-indigo-600 focus:ring-indigo-500" checked>
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">Pay Online</span>
                                <span class="block text-sm text-gray-500">Credit/Debit Card, UPI, Net Banking</span>
                            </div>
                            <div class="ml-auto">
                                <img src="https://razorpay.com/assets/razorpay-glyph.svg" alt="Razorpay" class="h-8">
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border border-gray-300 rounded-lg cursor-pointer hover:border-indigo-500">
                            <input type="radio" name="payment_method" value="cod" class="text-indigo-600 focus:ring-indigo-500">
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">Cash on Delivery</span>
                                <span class="block text-sm text-gray-500">Pay when you receive the order</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Customer Note -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Additional Information</h2>
                    
                    <div>
                        <label for="customer_note" class="block text-sm font-medium text-gray-700">Order Notes (Optional)</label>
                        <textarea id="customer_note" name="customer_note" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Any special instructions for your order...">{{ old('customer_note') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>
                    
                    <!-- Cart Items -->
                    <div class="space-y-4 mb-6">
                        @foreach($cartItems as $cartItem)
                            <div class="flex items-center space-x-3">
                                @if($cartItem->product->primaryImage)
                                    <img src="{{ asset('storage/' . $cartItem->product->primaryImage->image_path) }}" 
                                         alt="{{ $cartItem->product->name }}"
                                         class="w-12 h-12 object-cover rounded">
                                @else
                                    <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $cartItem->product->name }}
                                    </p>
                                    @if($cartItem->variant)
                                        <p class="text-xs text-gray-500">
                                            {{ $cartItem->variant->color_name }}
                                            @if($cartItem->variant->size)
                                                , {{ $cartItem->variant->size }}
                                            @endif
                                        </p>
                                    @endif
                                    <p class="text-xs text-gray-500">
                                        Qty: {{ $cartItem->quantity }} × ₹{{ $cartItem->price }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-gray-900">
                                    ₹{{ $cartItem->price * $cartItem->quantity }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Order Totals -->
                    <div class="space-y-2 border-t border-gray-200 pt-4">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">₹{{ $cartTotal }}</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium">₹0</span>
                        </div>
                        
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax</span>
                            <span class="font-medium">₹0</span>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-2">
                            <div class="flex justify-between text-lg font-semibold">
                                <span>Total</span>
                                <span class="text-indigo-600">₹{{ $cartTotal }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Place Order Button -->
                    <button type="submit" id="place-order-btn" 
                            class="w-full bg-indigo-600 text-white py-3 px-4 rounded-lg hover:bg-indigo-700 font-semibold mt-6 transition duration-200">
                        Place Order
                    </button>

                    <!-- Continue Shopping -->
                    <a href="{{ route('cart.index') }}" class="block text-center text-indigo-600 hover:text-indigo-700 mt-4 text-sm">
                        Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ Checkout page loaded');
    
    // Billing address toggle
    const billingSameCheckbox = document.getElementById('billing_same_as_shipping');
    const billingAddressFields = document.getElementById('billing-address-fields');

    if (billingSameCheckbox && billingAddressFields) {
        billingSameCheckbox.addEventListener('change', function() {
            if (this.checked) {
                billingAddressFields.classList.add('hidden');
                // Billing fields ko disable karein
                document.querySelectorAll('#billing-address-fields input, #billing-address-fields textarea').forEach(field => {
                    field.disabled = true;
                    field.removeAttribute('required');
                });
            } else {
                billingAddressFields.classList.remove('hidden');
                // Billing fields ko enable karein
                document.querySelectorAll('#billing-address-fields input, #billing-address-fields textarea').forEach(field => {
                    field.disabled = false;
                    field.setAttribute('required', 'required');
                });
            }
        });

        // Initial state set karein
        if (billingSameCheckbox.checked) {
            document.querySelectorAll('#billing-address-fields input, #billing-address-fields textarea').forEach(field => {
                field.disabled = true;
            });
        }
    }

    // Checkout form submission - FIXED VERSION
    const checkoutForm = document.getElementById('checkout-form');
    const placeOrderBtn = document.getElementById('place-order-btn');

    if (checkoutForm && placeOrderBtn) {
        checkoutForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('🟢 Form submission started');
            
            // Disable button and show loading
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = `
                <div class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </div>
            `;

            try {
                // Form data ko JSON mein convert karein - YEH FIXED HAI
                const formData = new FormData(this);
                const jsonData = {};
                
                for (let [key, value] of formData.entries()) {
                    // Checkbox value ko boolean mein convert karein
                    if (key === 'billing_same_as_shipping') {
                        jsonData[key] = value === '1';
                    } else {
                        jsonData[key] = value;
                    }
                }

                console.log('📋 Sending JSON data:', jsonData);

                const response = await fetch('{{ route("checkout.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(jsonData)
                });

                console.log('📨 Response status:', response.status);

                // Agar validation error aaye
                if (response.status === 422) {
                    const errorData = await response.json();
                    console.error('❌ Validation errors:', errorData);
                    
                    let errorMessage = 'Please check your form: ';
                    if (errorData.errors) {
                        const firstError = Object.values(errorData.errors)[0][0];
                        errorMessage += firstError;
                    } else {
                        errorMessage += 'Invalid form data';
                    }
                    
                    showNotification(errorMessage, 'error');
                    resetPlaceOrderButton();
                    return;
                }

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                console.log('✅ Checkout response:', data);

                if (data.success) {
                    if (data.payment_method === 'razorpay') {
                        await initiateRazorpayPayment(data);
                    } else {
                        showSuccessMessage(data.message, data.order_number);
                    }
                } else {
                    showNotification(data.message || 'Order processing failed', 'error');
                    resetPlaceOrderButton();
                }

            } catch (error) {
                console.error('❌ Fetch error:', error);
                showNotification('An error occurred while processing your order. Please try again.', 'error');
                resetPlaceOrderButton();
            }
        });
    }

    function resetPlaceOrderButton() {
        if (placeOrderBtn) {
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerHTML = 'Place Order';
        }
    }

    async function initiateRazorpayPayment(data) {
        console.log('💳 Initiating Razorpay payment:', data);
        
        if (!data.key || !data.razorpay_order_id || !data.amount) {
            showNotification('Payment configuration error. Please try again.', 'error');
            resetPlaceOrderButton();
            return;
        }

        const options = {
            key: data.key,
            amount: data.amount,
            currency: data.currency || 'INR',
            name: data.name || '{{ config("app.name") }}',
            description: data.description || 'Order Payment',
            order_id: data.razorpay_order_id,
            handler: async function(response) {
                console.log('✅ Razorpay payment success:', response);
                await handleRazorpaySuccess(response, data.order_id);
            },
            prefill: {
                name: data.prefill_name || '{{ $user->name }}',
                email: data.prefill_email || '{{ $user->email }}',
                contact: data.prefill_contact || '{{ $user->phone }}'
            },
            theme: {
                color: '#4F46E5'
            },
            modal: {
                ondismiss: function() {
                    console.log('❌ Razorpay modal dismissed');
                    handleRazorpayFailure(data.order_id);
                }
            }
        };

        try {
            const rzp = new Razorpay(options);
            rzp.open();
        } catch (error) {
            console.error('❌ Razorpay initialization error:', error);
            showNotification('Payment gateway error: ' + error.message, 'error');
            resetPlaceOrderButton();
        }
    }

    async function handleRazorpaySuccess(response, orderId) {
        showNotification('Verifying payment...', 'info');
        
        try {
            const verifyResponse = await fetch('{{ route("payment.razorpay.success") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    razorpay_payment_id: response.razorpay_payment_id,
                    razorpay_order_id: response.razorpay_order_id,
                    razorpay_signature: response.razorpay_signature,
                    order_id: orderId
                })
            });

            const data = await verifyResponse.json();
            
            if (data.success) {
                showSuccessMessage(data.message, data.order_number);
            } else {
                showNotification(data.message, 'error');
                resetPlaceOrderButton();
            }
        } catch (error) {
            console.error('❌ Payment verification error:', error);
            showNotification('Payment verification failed', 'error');
            resetPlaceOrderButton();
        }
    }

    async function handleRazorpayFailure(orderId) {
        try {
            const response = await fetch('{{ route("payment.razorpay.failure") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    order_id: orderId
                })
            });

            const data = await response.json();
            showNotification(data.message, 'error');
            resetPlaceOrderButton();
        } catch (error) {
            console.error('❌ Payment failure error:', error);
            showNotification('Payment failed', 'error');
            resetPlaceOrderButton();
        }
    }

    function showSuccessMessage(message, orderNumber) {
        console.log('🎉 Showing success message for order:', orderNumber);
        
        // Create success modal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center';
        modal.innerHTML = `
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6 text-center">
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Order Successful!</h3>
                    <p class="text-gray-600 mb-4">${message}</p>
                    <p class="text-sm text-gray-500 mb-6">Order Number: <span class="font-semibold">${orderNumber}</span></p>
                    <div class="space-y-3">
                        <a href="{{ route('orders.index') }}" 
                           class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition duration-200 block">
                            View Orders
                        </a>
                        <a href="{{ route('home') }}" 
                           class="w-full bg-gray-200 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-300 transition duration-200 block">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close modal on background click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    function showNotification(message, type = 'info') {
        // Remove existing notifications
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());

        const notification = document.createElement('div');
        notification.className = `notification fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transform transition-transform duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            type === 'info' ? 'bg-blue-500 text-white' : 'bg-gray-500 text-white'
        }`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => {
            notification.classList.add('translate-x-0');
        }, 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            notification.classList.add('opacity-0', 'translate-x-full');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }, 5000);
    }
});
</script>
@endsection