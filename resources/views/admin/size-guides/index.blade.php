@extends('layouts.admin')

@section('title', 'Size Guides')
@section('page-title', 'Size Guides')
@section('page-description', 'Manage size guide charts for products')

@section('header-actions')
<a href="{{ route('admin.size-guides.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
    <i class="fas fa-plus mr-2"></i>Add Size Guide
</a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($sizeGuides as $sizeGuide)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($sizeGuide->image_path)
                            <img src="{{ asset('storage/' . $sizeGuide->image_path) }}"
                                 alt="{{ $sizeGuide->title }}"
                                 class="h-16 w-16 object-cover rounded-lg border">
                        @else
                            <div class="h-16 w-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-ruler text-gray-400 text-xl"></i>
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $sizeGuide->title }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500 max-w-xs truncate">
                            {{ $sizeGuide->description ?? 'No description' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ $sizeGuide->products_count }} products
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <form action="{{ route('admin.size-guides.toggle-status', $sizeGuide) }}" method="POST" class="inline">
                            @csrf
                            @method('POST')
                            <button type="submit" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sizeGuide->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $sizeGuide->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('admin.size-guides.edit', $sizeGuide) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('admin.size-guides.destroy', $sizeGuide) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this size guide?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-ruler text-4xl mb-4 block text-gray-300"></i>
                        No size guides found. <a href="{{ route('admin.size-guides.create') }}" class="text-indigo-600 hover:underline">Create one</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $sizeGuides->links() }}
</div>
@endsection
