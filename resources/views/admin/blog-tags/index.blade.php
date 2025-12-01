@extends('layouts.admin')

@section('title', 'Manage Blog Tags')
@section('page-title', 'Blog Tags Management')

@section('header-actions')
    <a href="{{ route('admin.blog-tags.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>Add Blog Tag
    </a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <!-- Tags Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tag</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Blogs</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tags as $tag)
                    <tr>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-orange-100 rounded flex items-center justify-center mr-3">
                                    <i class="fas fa-hashtag text-orange-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $tag->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $tag->slug }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                {{ $tag->blogs_count }} blogs
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <form action="{{ route('admin.blog-tags.toggle-status', $tag) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $tag->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                    {{ $tag->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.blog-tags.edit', $tag) }}"
                                   class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.blog-tags.destroy', $tag) }}" method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this tag?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-hashtag text-4xl mb-4"></i>
                            <p>No blog tags found.</p>
                            <a href="{{ route('admin.blog-tags.create') }}" class="text-indigo-600 hover:text-indigo-800 mt-2 inline-block">
                                Create your first blog tag
                            </a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($tags->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $tags->links() }}
        </div>
    @endif
</div>
@endsection
