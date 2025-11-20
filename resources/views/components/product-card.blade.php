@props(['product'])

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
        
        <!-- Sale Badge -->
        @if($product->is_on_sale)
            <span class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 text-xs font-bold rounded">
                -{{ $product->discount_percentage }}%
            </span>
        @endif
        
        <!-- Featured Badge -->
        @if($product->is_featured)
            <span class="absolute top-2 right-2 bg-indigo-500 text-white px-2 py-1 text-xs font-bold rounded">
                Featured
            </span>
        @endif
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
        
        <!-- Short Description -->
        @if($product->short_description)
            <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                {{ $product->short_description }}
            </p>
        @endif
        
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
            
            <!-- Stock Status -->
            <div class="text-xs {{ $product->in_stock ? 'text-green-600' : 'text-red-600' }}">
                {{ $product->in_stock ? 'In Stock' : 'Out of Stock' }}
            </div>
        </div>
        
        <!-- Variants Preview -->
        @if($product->variants->count() > 0)
            <div class="flex items-center space-x-1 mb-3">
                @foreach($product->variants->take(3) as $variant)
                    @if($variant->color_code)
                        <div class="w-4 h-4 rounded-full border border-gray-300" 
                             style="background-color: {{ $variant->color_code }}"
                             title="{{ $variant->color_name }}"></div>
                    @endif
                @endforeach
                @if($product->variants->count() > 3)
                    <span class="text-xs text-gray-500">+{{ $product->variants->count() - 3 }} more</span>
                @endif
            </div>
        @endif
        
        <!-- Action Buttons -->
        <div class="flex space-x-2">
            <button class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
                Add to Cart
            </button>
            <button class="w-10 h-10 border border-gray-300 rounded-lg hover:border-indigo-600 hover:text-indigo-600 flex items-center justify-center">
                <i class="far fa-heart"></i>
            </button>
        </div>
    </div>
</div>