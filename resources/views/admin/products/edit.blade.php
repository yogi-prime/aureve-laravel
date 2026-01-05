@extends('layouts.admin')

@section('title', 'Edit Product: ' . $product->name)
@section('page-title', 'Edit Product: ' . $product->name)

@section('styles')
<style>
    .variant-section {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f9fafb;
    }
    .image-preview {
        display: inline-block;
        position: relative;
        margin: 0.5rem;
    }
    .image-preview img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 0.375rem;
        border: 1px solid #e5e7eb;
    }
    .remove-image {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #ef4444;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        cursor: pointer;
    }
</style>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
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
                               value="{{ old('name', $product->name) }}">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SKU -->
                    <div>
                        <label for="sku" class="block text-sm font-medium text-gray-700">SKU *</label>
                        <input type="text" name="sku" id="sku" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('sku', $product->sku) }}">
                        @error('sku')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
                        <select name="category_id" id="category_id" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Size Guide -->
                    <div>
                        <label for="size_guide_id" class="block text-sm font-medium text-gray-700">Size Guide</label>
                        <select name="size_guide_id" id="size_guide_id"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">No Size Guide</option>
                            @foreach($sizeGuides as $sizeGuide)
                                <option value="{{ $sizeGuide->id }}" {{ old('size_guide_id', $product->size_guide_id) == $sizeGuide->id ? 'selected' : '' }}>
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
                               value="{{ old('brand', $product->brand) }}">
                        @error('brand')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Base Price -->
                    <div>
                        <label for="base_price" class="block text-sm font-medium text-gray-700">Base Price (₹) *</label>
                        <input type="number" name="base_price" id="base_price" step="0.01" min="0" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('base_price', $product->base_price) }}">
                        @error('base_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Sale Price -->
                    <div>
                        <label for="sale_price" class="block text-sm font-medium text-gray-700">Sale Price (₹)</label>
                        <input type="number" name="sale_price" id="sale_price" step="0.01" min="0"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('sale_price', $product->sale_price) }}">
                        @error('sale_price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Stock Quantity -->
                    <div>
                        <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Stock Quantity *</label>
                        <input type="number" name="stock_quantity" id="stock_quantity" min="0" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('stock_quantity', $product->stock_quantity) }}">
                        @error('stock_quantity')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Model -->
                    <div>
                        <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                        <input type="text" name="model" id="model"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('model', $product->model) }}">
                        @error('model')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('short_description', $product->short_description) }}</textarea>
                        @error('short_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Full Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Full Description *</label>
                        <textarea name="description" id="description" rows="6" required
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                    @foreach($product->variants as $index => $variant)
                        <div class="variant-section" id="variant-{{ $index + 1 }}">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="font-medium text-gray-900">Variant {{ $index + 1 }}</h4>
                                <button type="button" onclick="removeVariant({{ $index + 1 }})" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <input type="hidden" name="variants[{{ $index + 1 }}][id]" value="{{ $variant->id }}">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Color Name *</label>
                                    <input type="text" name="variants[{{ $index + 1 }}][color_name]" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2"
                                           value="{{ old('variants.'.($index+1).'.color_name', $variant->color_name) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Color Code</label>
                                    <input type="color" name="variants[{{ $index + 1 }}][color_code]"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 h-10"
                                           value="{{ old('variants.'.($index+1).'.color_code', $variant->color_code) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Size</label>
                                    <input type="text" name="variants[{{ $index + 1 }}][size]"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2"
                                           value="{{ old('variants.'.($index+1).'.size', $variant->size) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Material</label>
                                    <input type="text" name="variants[{{ $index + 1 }}][material]"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2"
                                           value="{{ old('variants.'.($index+1).'.material', $variant->material) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Weight (grams)</label>
                                    <input type="number" name="variants[{{ $index + 1 }}][weight]" step="0.01" min="0"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2"
                                           placeholder="e.g. 150"
                                           value="{{ old('variants.'.($index+1).'.weight', $variant->weight) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Price (₹) *</label>
                                    <input type="number" name="variants[{{ $index + 1 }}][price]" step="0.01" min="0" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2"
                                           value="{{ old('variants.'.($index+1).'.price', $variant->price) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sale Price (₹)</label>
                                    <input type="number" name="variants[{{ $index + 1 }}][sale_price]" step="0.01" min="0"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2"
                                           value="{{ old('variants.'.($index+1).'.sale_price', $variant->sale_price) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Stock Quantity *</label>
                                    <input type="number" name="variants[{{ $index + 1 }}][stock_quantity]" min="0" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2"
                                           value="{{ old('variants.'.($index+1).'.stock_quantity', $variant->stock_quantity) }}">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">SKU *</label>
                                    <input type="text" name="variants[{{ $index + 1 }}][sku]" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2"
                                           value="{{ old('variants.'.($index+1).'.sku', $variant->sku) }}">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Variant Description</label>
                                    <textarea name="variants[{{ $index + 1 }}][variant_description]" rows="2"
                                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2">{{ old('variants.'.($index+1).'.variant_description', $variant->variant_description) }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
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
                               value="{{ old('meta_title', $product->seo->meta_title ?? '') }}">
                        @error('meta_title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="meta_description" class="block text-sm font-medium text-gray-700">Meta Description</label>
                        <textarea name="meta_description" id="meta_description" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('meta_description', $product->seo->meta_description ?? '') }}</textarea>
                        @error('meta_description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="focus_keyword" class="block text-sm font-medium text-gray-700">Focus Keyword</label>
                        <input type="text" name="focus_keyword" id="focus_keyword"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('focus_keyword', $product->seo->focus_keyword ?? '') }}">
                        @error('focus_keyword')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="meta_keywords"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('meta_keywords', $product->seo->meta_keywords ?? '') }}" 
                               placeholder="diamond, ring, jewellery, 18k gold, luxury, women, engagement, gift, auriccraft">
                        @error('meta_keywords')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="seo_content" class="block text-sm font-medium text-gray-700">SEO Content</label>
                        <textarea name="seo_content" id="seo_content" rows="4"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('seo_content', $product->seo->seo_content ?? '') }}</textarea>
                        @error('seo_content')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Product Images -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Product Images</h3>
                
                <!-- Existing Images -->
                @if($product->images->count() > 0)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Images</label>
                        <div class="flex flex-wrap">
                            @foreach($product->images as $image)
                                <div class="image-preview">
                                    <img src="{{ Storage::disk('public')->url($image->image_path) }}" 
                                         alt="{{ $image->alt_text }}">
                                    <div class="remove-image" onclick="removeExistingImage({{ $image->id }})">
                                        ×
                                    </div>
                                    <div class="text-center mt-1">
                                        <label class="flex items-center justify-center">
                                            <input type="radio" name="primary_image" value="{{ $image->id }}" 
                                                   {{ $image->is_primary ? 'checked' : '' }} class="mr-1">
                                            <span class="text-xs">Primary</span>
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- New Images Upload -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Add New Images</label>
                    <input type="file" name="images[]" id="images" multiple
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                           accept="image/*">
                    @error('images.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Images to Remove -->
                <input type="hidden" name="removed_images" id="removed-images" value="">
            </div>

            <!-- Tags -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Tags</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    @foreach($tags as $tag)
                        <label class="flex items-center">
                            <input type="checkbox" name="tags[]" value="{{ $tag->id }}" 
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                   {{ in_array($tag->id, $product->tags->pluck('id')->toArray()) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">{{ $tag->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('tags')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
                <div class="flex space-x-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_featured" value="1" 
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               {{ $product->is_featured ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">Featured Product</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" 
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               {{ $product->is_active ? 'checked' : '' }}>
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
                Update Product
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let variantCount = {{ $product->variants->count() }};

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

    // Handle existing image removal
    function removeExistingImage(imageId) {
        const removedImagesInput = document.getElementById('removed-images');
        let removedImages = removedImagesInput.value ? removedImagesInput.value.split(',') : [];
        
        if (!removedImages.includes(imageId.toString())) {
            removedImages.push(imageId.toString());
            removedImagesInput.value = removedImages.join(',');
        }
        
        // Hide the image preview
        const imageElement = document.querySelector(`.image-preview img[src*="${imageId}"]`);
        if (imageElement) {
            imageElement.closest('.image-preview').style.display = 'none';
        }
    }

    // Initialize variant count if no variants exist
    document.addEventListener('DOMContentLoaded', function() {
        if (variantCount === 0) {
            addVariant();
        }
    });
</script>
@endsection