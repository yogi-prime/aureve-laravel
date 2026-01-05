@extends('layouts.admin')

@section('title', 'Add New Collection')
@section('page-title', 'Add New Collection')

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <form action="{{ route('admin.collections.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="p-6 space-y-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Collection Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Collection Name *</label>
                        <input type="text" name="name" id="name" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('name') }}">
                    </div>

                    <!-- Parent Collection -->
                    <div>
                        <label for="parent_id" class="block text-sm font-medium text-gray-700">Parent Collection</label>
                        <select name="parent_id" id="parent_id"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">No Parent (Main Collection)</option>
                            @foreach($parentCollections as $parent)
                                <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort Order -->
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" min="0"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('sort_order', 0) }}">
                    </div>

                    <!-- Collection Image -->
                    <div>
                        <label for="image" class="block text-sm font-medium text-gray-700">Collection Image</label>
                        <input type="file" name="image" id="image" accept="image/*"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    </div>
                </div>
            </div>

            <!-- Description -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Description</h3>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="4"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Products Selection -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Select Products</h3>
                <p class="text-sm text-gray-500 mb-4">Select products to include in this collection</p>
                <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-md p-3">
                    <div class="space-y-2">
                        @foreach($products as $product)
                            <label class="flex items-center p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="products[]" value="{{ $product->id }}"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       {{ in_array($product->id, old('products', [])) ? 'checked' : '' }}>
                                <span class="ml-3 text-sm text-gray-700">
                                    {{ $product->name }}
                                    <span class="text-gray-400">({{ $product->sku }})</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
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
                        <label for="meta_keywords" class="block text-sm font-medium text-gray-700">Meta Keywords</label>
                        <input type="text" name="meta_keywords" id="meta_keywords"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('meta_keywords') }}"
                               placeholder="keyword1, keyword2, keyword3">
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Active Collection</span>
                </label>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.collections.index') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                Create Collection
            </button>
        </div>
    </form>
</div>
@endsection
