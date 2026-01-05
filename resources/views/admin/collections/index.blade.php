@extends('layouts.admin')

@section('title', 'Manage Collections')
@section('page-title', 'Collections Management')

@section('header-actions')
    <a href="{{ route('admin.collections.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>Add Collection
    </a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Collections Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collection</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Parent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sort Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($collections as $collection)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($collection->image)
                                    <img src="{{ asset('storage/' . $collection->image) }}"
                                         alt="{{ $collection->name }}"
                                         class="w-10 h-10 rounded object-cover mr-3">
                                @else
                                    <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center mr-3">
                                        <i class="fas fa-layer-group text-gray-400"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-medium text-gray-900">{{ $collection->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $collection->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $collection->parent->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">
                                {{ $collection->products_count }} products
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $collection->sort_order }}
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.collections.toggle-status', $collection) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $collection->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                    {{ $collection->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.collections.manage-products', $collection) }}"
                                   class="text-purple-600 hover:text-purple-900" title="Manage Products">
                                    <i class="fas fa-boxes"></i>
                                </a>
                                <a href="{{ route('admin.collections.edit', $collection) }}"
                                   class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.collections.destroy', $collection) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this collection?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    <!-- Sub-collections -->
                    @foreach($collection->children as $child)
                        <tr class="bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center ml-6">
                                    @if($child->image)
                                        <img src="{{ asset('storage/' . $child->image) }}"
                                             alt="{{ $child->name }}"
                                             class="w-8 h-8 rounded object-cover mr-3">
                                    @else
                                        <div class="w-8 h-8 bg-gray-300 rounded flex items-center justify-center mr-3">
                                            <i class="fas fa-layer-group text-gray-500 text-xs"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $child->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $child->slug }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $collection->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">
                                    {{ $child->products_count }} products
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $child->sort_order }}
                            </td>
                            <td class="px-6 py-4">
                                <form action="{{ route('admin.collections.toggle-status', $child) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $child->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                        {{ $child->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.collections.manage-products', $child) }}"
                                       class="text-purple-600 hover:text-purple-900" title="Manage Products">
                                        <i class="fas fa-boxes"></i>
                                    </a>
                                    <a href="{{ route('admin.collections.edit', $child) }}"
                                       class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.collections.destroy', $child) }}" method="POST"
                                          onsubmit="return confirm('Are you sure you want to delete this collection?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($collections->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $collections->links() }}
        </div>
    @endif
</div>
@endsection
