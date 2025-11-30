@extends('layouts.admin')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <!-- Stats Cards -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 bg-indigo-100 rounded-lg">
                <i class="fas fa-box text-indigo-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Products</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $totalProducts }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
                <i class="fas fa-tags text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Categories</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $totalCategories }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
                <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Orders</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $totalOrders }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-lg">
                <i class="fas fa-blog text-purple-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <h3 class="text-sm font-medium text-gray-500">Total Blogs</h3>
                <p class="text-2xl font-bold text-gray-900">{{ $totalBlogs ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Products -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Recent Products</h3>
            <a href="{{ route('admin.products.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View All</a>
        </div>
        <div class="p-6">
            @if($recentProducts->count() > 0)
                <div class="space-y-4">
                    @foreach($recentProducts as $product)
                        <div class="flex items-center">
                            @if($product->primaryImage)
                                <img src="{{ asset('storage/' . $product->primaryImage->image_path) }}"
                                     alt="{{ $product->name }}"
                                     class="w-12 h-12 rounded object-cover mr-4">
                            @else
                                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center mr-4">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                            @endif
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ Str::limit($product->name, 30) }}</div>
                                <div class="text-sm text-gray-500">{{ $product->category->name ?? 'Uncategorized' }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium text-gray-900">{{ number_format($product->base_price) }}</div>
                                <span class="text-xs px-2 py-1 rounded {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-box text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">No products yet</p>
                    <a href="{{ route('admin.products.create') }}" class="text-indigo-600 hover:text-indigo-800">Add Product</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Blogs -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Recent Blogs</h3>
            <a href="{{ route('admin.blogs.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">View All</a>
        </div>
        <div class="p-6">
            @if(isset($recentBlogs) && $recentBlogs->count() > 0)
                <div class="space-y-4">
                    @foreach($recentBlogs as $blog)
                        <div class="flex items-center">
                            @if($blog->featured_image)
                                <img src="{{ asset('storage/' . $blog->featured_image) }}"
                                     alt="{{ $blog->title }}"
                                     class="w-12 h-12 rounded object-cover mr-4">
                            @else
                                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center mr-4">
                                    <i class="fas fa-blog text-gray-400"></i>
                                </div>
                            @endif
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ Str::limit($blog->title, 30) }}</div>
                                <div class="text-sm text-gray-500">{{ $blog->reading_time }} min read</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">{{ $blog->view_count }} views</div>
                                <span class="text-xs px-2 py-1 rounded {{ $blog->is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $blog->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-blog text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">No blogs yet</p>
                    <a href="{{ route('admin.blogs.create') }}" class="text-indigo-600 hover:text-indigo-800">Add Blog</a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
