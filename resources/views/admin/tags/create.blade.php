@extends('layouts.admin')

@section('title', 'Add New Tag')
@section('page-title', 'Add New Tag')

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <form action="{{ route('admin.tags.store') }}" method="POST">
        @csrf
        
        <div class="p-6 space-y-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 gap-6">
                    <!-- Tag Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Tag Name *</label>
                        <input type="text" name="name" id="name" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               value="{{ old('name') }}"
                               placeholder="Enter tag name">
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="4"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Enter tag description">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Active Tag</span>
                </label>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.tags.index') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                Create Tag
            </button>
        </div>
    </form>
</div>
@endsection