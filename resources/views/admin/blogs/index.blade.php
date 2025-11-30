@extends('layouts.admin')

@section('title', 'Blog Management')
@section('page-title', 'Blog Management')
@section('page-description', 'Manage your blog posts and insights')

@section('header-actions')
    <a href="{{ route('admin.blogs.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-plus mr-2"></i>Add New Blog
    </a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Blog</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SEO</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Views</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($blogs as $blog)
            <tr>
                <td class="px-6 py-4">
                    <div class="flex items-center">
                        @if($blog->featured_image)
                        <img src="{{ asset('storage/' . $blog->featured_image) }}" alt="{{ $blog->title }}" class="h-12 w-16 object-cover rounded mr-4">
                        @else
                        <div class="h-12 w-16 bg-gray-200 rounded mr-4 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400"></i>
                        </div>
                        @endif
                        <div>
                            <div class="font-medium text-gray-900">{{ Str::limit($blog->title, 40) }}</div>
                            <div class="text-sm text-gray-500">{{ $blog->reading_time }} min read</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    @if($blog->category)
                    <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ $blog->category->name }}</span>
                    @else
                    <span class="text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex flex-col space-y-1">
                        <form action="{{ route('admin.blogs.toggle-status', $blog) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-2 py-1 text-xs rounded {{ $blog->is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $blog->is_published ? 'Published' : 'Draft' }}
                            </button>
                        </form>
                        @if($blog->is_featured)
                        <span class="px-2 py-1 text-xs bg-purple-100 text-purple-800 rounded">Featured</span>
                        @endif
                    </div>
                </td>
                <td class="px-6 py-4">
                    @php
                        $seoScore = app(\App\Http\Controllers\Admin\BlogController::class)->calculateSeoScore($blog);
                    @endphp
                    <div class="flex items-center">
                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                            <div class="h-2 rounded-full {{ $seoScore['percentage'] >= 80 ? 'bg-green-500' : ($seoScore['percentage'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                 style="width: {{ $seoScore['percentage'] }}%"></div>
                        </div>
                        <span class="text-sm {{ $seoScore['percentage'] >= 80 ? 'text-green-600' : ($seoScore['percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                            {{ $seoScore['percentage'] }}%
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ number_format($blog->view_count) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ $blog->published_at ? $blog->published_at->format('M d, Y') : '-' }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-indigo-600 hover:text-indigo-900">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.blogs.toggle-featured', $blog) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-purple-600 hover:text-purple-900" title="{{ $blog->is_featured ? 'Remove from featured' : 'Mark as featured' }}">
                                <i class="fas {{ $blog->is_featured ? 'fa-star' : 'fa-star' }} {{ $blog->is_featured ? 'text-yellow-500' : '' }}"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this blog?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <i class="fas fa-blog text-4xl mb-4"></i>
                    <p>No blogs found. Create your first blog post!</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-6">
    {{ $blogs->links() }}
</div>
@endsection
