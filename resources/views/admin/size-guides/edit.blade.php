@extends('layouts.admin')

@section('title', 'Edit Size Guide')
@section('page-title', 'Edit Size Guide')

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <form action="{{ route('admin.size-guides.update', $sizeGuide) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="p-6 space-y-6">
            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title *</label>
                <input type="text" name="title" id="title" required
                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                       value="{{ old('title', $sizeGuide->title) }}">
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $sizeGuide->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Current Image -->
            @if($sizeGuide->image_path)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Current Image</label>
                <div class="border rounded-lg p-4 bg-gray-50">
                    <img src="{{ asset('storage/' . $sizeGuide->image_path) }}"
                         alt="{{ $sizeGuide->title }}"
                         class="max-h-64 object-contain rounded">
                </div>
            </div>
            @endif

            <!-- Image Upload -->
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">{{ $sizeGuide->image_path ? 'Replace Image' : 'Size Chart Image' }}</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md" id="dropzone">
                    <div class="space-y-1 text-center">
                        <div id="preview-container" class="hidden mb-4">
                            <img id="image-preview" src="" alt="Preview" class="mx-auto h-48 object-contain">
                        </div>
                        <svg class="mx-auto h-12 w-12 text-gray-400" id="upload-icon" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none">
                                <span>Upload a new file</span>
                                <input id="image" name="image" type="file" class="sr-only" accept="image/*" onchange="previewImage(event)">
                            </label>
                            <p class="pl-1">or drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, GIF, WEBP up to 5MB</p>
                    </div>
                </div>
                @error('image')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Sort Order -->
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" min="0"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                           value="{{ old('sort_order', $sizeGuide->sort_order) }}">
                </div>

                <!-- Status -->
                <div class="flex items-center pt-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ $sizeGuide->is_active ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
            </div>

            <!-- Products Using This Guide -->
            @if($sizeGuide->products()->count() > 0)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Products Using This Size Guide</label>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex flex-wrap gap-2">
                        @foreach($sizeGuide->products()->limit(10)->get() as $product)
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm hover:bg-indigo-200">
                                {{ $product->name }}
                            </a>
                        @endforeach
                        @if($sizeGuide->products()->count() > 10)
                            <span class="px-3 py-1 bg-gray-200 text-gray-600 rounded-full text-sm">
                                +{{ $sizeGuide->products()->count() - 10 }} more
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Form Actions -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('admin.size-guides.index') }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700">
                Update Size Guide
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('image-preview').src = e.target.result;
            document.getElementById('preview-container').classList.remove('hidden');
            document.getElementById('upload-icon').classList.add('hidden');
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
