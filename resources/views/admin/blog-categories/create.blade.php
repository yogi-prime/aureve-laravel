@extends('layouts.admin')

@section('title', 'Create Blog Category')
@section('page-title', 'Create Blog Category')

@section('header-actions')
    <a href="{{ route('admin.blog-categories.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
        <i class="fas fa-arrow-left mr-2"></i>Back to Categories
    </a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
    <form action="{{ route('admin.blog-categories.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="flex items-center pt-6">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                       {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- SEO Section -->
            <div class="md:col-span-2 border-t pt-6 mt-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4"><i class="fas fa-search mr-2"></i>SEO Settings</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- SEO Title -->
                    <div>
                        <label for="seo_title" class="block text-sm font-medium text-gray-700 mb-1">SEO Title</label>
                        <input type="text" name="seo_title" id="seo_title" value="{{ old('seo_title') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Leave empty to use category name</p>
                    </div>

                    <!-- SEO Description -->
                    <div>
                        <label for="seo_description" class="block text-sm font-medium text-gray-700 mb-1">SEO Description</label>
                        <textarea name="seo_description" id="seo_description" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">{{ old('seo_description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('admin.blog-categories.index') }}"
               class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                <i class="fas fa-save mr-2"></i>Create Category
            </button>
        </div>
    </form>
</div>
@endsection
