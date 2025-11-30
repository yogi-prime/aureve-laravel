@extends('layouts.admin')

@section('title', $blog->title)
@section('page-title', 'View Blog')

@section('header-actions')
    <a href="{{ route('admin.blogs.edit', $blog) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
        <i class="fas fa-edit mr-2"></i>Edit
    </a>
    <a href="{{ route('admin.blogs.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
        <i class="fas fa-arrow-left mr-2"></i>Back
    </a>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Blog Content -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($blog->featured_image)
            <img src="{{ asset('storage/' . $blog->featured_image) }}" alt="{{ $blog->featured_image_alt }}"
                 class="w-full h-64 object-cover">
            @endif

            <div class="p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $blog->title }}</h1>

                <div class="flex items-center space-x-4 text-sm text-gray-500 mb-6">
                    @if($blog->category)
                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded">{{ $blog->category->name }}</span>
                    @endif
                    <span><i class="fas fa-clock mr-1"></i>{{ $blog->reading_time }} min read</span>
                    <span><i class="fas fa-eye mr-1"></i>{{ number_format($blog->view_count) }} views</span>
                    @if($blog->published_at)
                    <span><i class="fas fa-calendar mr-1"></i>{{ $blog->published_at->format('M d, Y') }}</span>
                    @endif
                </div>

                @if($blog->excerpt)
                <p class="text-gray-600 italic mb-6 border-l-4 border-indigo-500 pl-4">{{ $blog->excerpt }}</p>
                @endif

                <div class="prose max-w-none">
                    {!! $blog->content !!}
                </div>

                @if($blog->tags->count() > 0)
                <div class="mt-6 pt-6 border-t">
                    <h4 class="font-semibold mb-2">Tags:</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($blog->tags as $tag)
                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm">{{ $tag->name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- SEO Score -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">SEO Score</h3>
            <div class="text-center mb-4">
                <span class="text-5xl font-bold {{ $seoScore['percentage'] >= 80 ? 'text-green-600' : ($seoScore['percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $seoScore['percentage'] }}%
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                <div class="h-4 rounded-full {{ $seoScore['percentage'] >= 80 ? 'bg-green-500' : ($seoScore['percentage'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                     style="width: {{ $seoScore['percentage'] }}%"></div>
            </div>
            <p class="text-center text-lg font-semibold mb-4">Grade: {{ $seoScore['grade'] }}</p>
        </div>

        <!-- Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Status</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Published:</span>
                    <span class="px-2 py-1 text-xs rounded {{ $blog->is_published ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $blog->is_published ? 'Yes' : 'Draft' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Featured:</span>
                    <span class="px-2 py-1 text-xs rounded {{ $blog->is_featured ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $blog->is_featured ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">Indexing:</span>
                    <span class="text-sm">{{ $blog->robots_index ? 'Allowed' : 'Blocked' }}</span>
                </div>
            </div>
        </div>

        <!-- Author -->
        @if($blog->author_name)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Author</h3>
            <div class="flex items-center">
                @if($blog->author_image)
                <img src="{{ asset('storage/' . $blog->author_image) }}" alt="{{ $blog->author_name }}"
                     class="w-12 h-12 rounded-full object-cover mr-3">
                @else
                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-user text-gray-400"></i>
                </div>
                @endif
                <div>
                    <p class="font-medium">{{ $blog->author_name }}</p>
                    @if($blog->author_bio)
                    <p class="text-sm text-gray-500">{{ Str::limit($blog->author_bio, 50) }}</p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- SEO Preview -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Google Preview</h3>
            <div class="border rounded-lg p-4 bg-gray-50">
                <p class="text-blue-600 text-lg hover:underline cursor-pointer truncate">
                    {{ $blog->seo_title ?: $blog->title }}
                </p>
                <p class="text-green-700 text-sm truncate">
                    https://aureve.com/insights/{{ $blog->slug }}
                </p>
                <p class="text-gray-600 text-sm line-clamp-2">
                    {{ $blog->seo_description ?: Str::limit(strip_tags($blog->content), 160) }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
