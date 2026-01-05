@extends('layouts.admin')

@section('title', 'Add New Product')
@section('page-title', 'Add New Product')

@section('styles')
<style>
    .variant-section {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f9fafb;
    }
</style>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="p-6 space-y-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Product Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
                        <input type="text" name="name" id="name" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('name') }}">
                    </div>

                    <!-- SKU -->
                    <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700">SKU *</label>
                        <input type="text" name="sku" id="sku" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('sku') }}">
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
                        <select name="category_id" id="category_id" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    
                    <!-- Size Guide -->
                    <div>
                        <label for="size_guide_id" class="block text-sm font-medium text-gray-700">Size Guide</label>
                        <select name="size_guide_id" id="size_guide_id"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">No Size Guide</option>
                            @foreach($sizeGuides as $sizeGuide)
                                <option value="{{ $sizeGuide->id }}" {{ old('size_guide_id') == $sizeGuide->id ? 'selected' : '' }}>
                                    {{ $sizeGuide->title }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            <a href="{{ route('admin.size-guides.create') }}" class="text-indigo-600 hover:underline">Create new size guide</a>
                        </p>
                    </div>

                    <!-- Brand -->
                    <div>
                        <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
                        <input type="text" name="brand" id="brand"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('brand') }}">
                    </div>

                    <!-- Base Price -->
                    <div>
                        <label for="base_price" class="block text-sm font-medium text-gray-700">Base Price (₹) *</label>
                        <input type="number" name="base_price" id="base_price" step="0.01" min="0" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('base_price') }}">
                    </div>

                    <!-- Sale Price -->
                    <div>
                        <label for="sale_price" class="block text-sm font-medium text-gray-700">Sale Price (₹)</label>
                        <input type="number" name="sale_price" id="sale_price" step="0.01" min="0"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('sale_price') }}">
                    </div>

                    <!-- Stock Quantity -->
                    <div>
                        <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Stock Quantity *</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" min="0" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('stock_quantity', 0) }}">
                    </div>

                    <!-- Model -->
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                        <input type="text" name="model" id="model"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('model') }}">
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Description</h3>
                <div class="space-y-4">
                    <!-- Short Description -->
                    <div>
                        <label for="short_description" class="block text-sm font-medium text-gray-700">Short Description</label>
                        <textarea name="short_description" id="short_description" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('short_description') }}</textarea>
                    </div>

                    <!-- Full Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Full Description *</label>
                        <textarea name="description" id="description" rows="6" required
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Product Variants -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Product Variants</h3>
                    <button type="button" onclick="addVariant()" class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700">
                        <i class="fas fa-plus mr-1"></i>Add Variant
                    </button>
                </div>
                
                <div id="variants-container">
                    <!-- Variants will be added here dynamically -->
                </div>
            </div>

            <!-- SEO Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Information</h3>
                <div class="space-y-4">
                    <div>
                        <label for="meta_title" class="block text-sm font-medium text-gray-700">Meta Title</label>
                        <input type="text" name="meta_title" id="meta_title"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('meta_title') }}">
                    </div>

                    <div>
                        <label for="meta_description" class="block text-sm font-medium text-gray-700">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('meta_description') }}</textarea>
                    </div>

                    <div>
                        <label for="focus_keyword" class="block text-sm font-medium text-gray-700">Focus Keyword</label>
                        <input type="text" name="focus_keyword" id="focus_keyword"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('focus_keyword') }}">
                    </div>
                    <label for="meta_keywords" class="block text-sm font-medium text-gray-700">Meta Keywords</label>
    <input type="text" name="meta_keywords" id="meta_keywords"
           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
           value="{{ old('meta_keywords') }}" placeholder="diamond, ring, jewellery, 18k gold, luxury, women, engagement, gift, auriccraft">
                </div>
            </div>

            <!-- Product Images -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Product Images</h3>
                <input type="file" name="images[]" id="images" multiple
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                       accept="image/*">
            </div>

            <!-- Tags -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tags</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($tags as $tag)
                        <label class="flex items-center">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" 
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Status -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
                <div class="flex space-x-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_featured" value="1" 
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Featured Product</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.products.index') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                Create Product
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let variantCount = 0;

    function addVariant() {
    variantCount++;
    const variantHtml = `
        <div class="variant-section" id="variant-${variantCount}">
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-medium text-gray-900">Variant ${variantCount}</h4>
                <button type="button" onclick="removeVariant(${variantCount})" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Color Name *</label>
                    <input type="text" name="variants[${variantCount}][color_name]" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Color Code</label>
                    <input type="color" name="variants[${variantCount}][color_code]"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 h-10">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Size</label>
                    <input type="text" name="variants[${variantCount}][size]"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Material</label>
                    <input type="text" name="variants[${variantCount}][material]"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Weight (grams)</label>
                    <input type="number" name="variants[${variantCount}][weight]" step="0.01" min="0"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2"
                           placeholder="e.g. 150">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Price (₹) *</label>
                    <input type="number" name="variants[${variantCount}][price]" step="0.01" min="0" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Sale Price (₹)</label>
                    <input type="number" name="variants[${variantCount}][sale_price]" step="0.01" min="0"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stock Quantity *</label>
                    <input type="number" name="variants[${variantCount}][stock_quantity]" min="0" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SKU *</label>
                    <input type="text" name="variants[${variantCount}][sku]" required
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Variant Description</label>
                    <textarea name="variants[${variantCount}][variant_description]" rows="2"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2"></textarea>
                </div>
            </div>
        </div>
    `;
    document.getElementById('variants-container').insertAdjacentHTML('beforeend', variantHtml);
}


    function removeVariant(variantId) {
        const variantElement = document.getElementById(`variant-${variantId}`);
        if (variantElement) {
            variantElement.remove();
        }
    }

    // Add one variant by default when page loads
    document.addEventListener('DOMContentLoaded', function() {
        addVariant();
    });
</script>
@endsection