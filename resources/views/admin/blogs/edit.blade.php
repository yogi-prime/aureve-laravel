@extends('layouts.admin')

@section('title', 'Edit Blog')
@section('page-title', 'Edit Blog')
@section('page-description', 'Update blog post and SEO settings')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
@endsection

@section('header-actions')
    <a href="{{ route('admin.blogs.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
        <i class="fas fa-arrow-left mr-2"></i>Back to List
    </a>
@endsection

@section('content')
<form action="{{ route('admin.blogs.update', $blog) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- SEO Score Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">SEO Score</h3>
                    <span class="text-3xl font-bold {{ $seoScore['percentage'] >= 80 ? 'text-green-600' : ($seoScore['percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                        {{ $seoScore['grade'] }}
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4 mb-4">
                    <div class="h-4 rounded-full {{ $seoScore['percentage'] >= 80 ? 'bg-green-500' : ($seoScore['percentage'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}"
                         style="width: {{ $seoScore['percentage'] }}%"></div>
                </div>
                <p class="text-gray-600 mb-4">{{ $seoScore['percentage'] }}% optimized</p>

                <div class="space-y-2 max-h-48 overflow-y-auto">
                    @foreach($seoScore['feedback'] as $item)
                    <div class="flex items-start text-sm">
                        @if($item['type'] === 'success')
                        <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                        @elseif($item['type'] === 'warning')
                        <i class="fas fa-exclamation-circle text-yellow-500 mr-2 mt-0.5"></i>
                        @else
                        <i class="fas fa-times-circle text-red-500 mr-2 mt-0.5"></i>
                        @endif
                        <span class="{{ $item['type'] === 'success' ? 'text-green-700' : ($item['type'] === 'warning' ? 'text-yellow-700' : 'text-red-700') }}">
                            {{ $item['message'] }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Basic Information</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" value="{{ old('title', $blog->title) }}" required
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Enter blog title" id="blog-title">
                        @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $blog->slug) }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="auto-generated-from-title" id="blog-slug">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                        <textarea name="excerpt" rows="3"
                                  class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Brief summary of the blog post" maxlength="500">{{ old('excerpt', $blog->excerpt) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                        <textarea name="content" id="content" rows="15"
                                  class="w-full border rounded-lg px-3 py-2">{{ old('content', $blog->content) }}</textarea>
                        @error('content')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- SEO Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-search text-indigo-600 mr-2"></i>SEO Settings
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Title</label>
                        <input type="text" name="seo_title" value="{{ old('seo_title', $blog->seo_title) }}" maxlength="70"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="SEO optimized title (50-60 characters ideal)" id="seo-title">
                        <div class="flex justify-between mt-1">
                            <p class="text-gray-500 text-xs">50-60 characters recommended</p>
                            <p class="text-xs"><span id="seo-title-count">{{ strlen($blog->seo_title ?? '') }}</span>/70</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Description</label>
                        <textarea name="seo_description" rows="2" maxlength="160"
                                  class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Meta description for search engines" id="seo-description">{{ old('seo_description', $blog->seo_description) }}</textarea>
                        <div class="flex justify-between mt-1">
                            <p class="text-gray-500 text-xs">120-160 characters recommended</p>
                            <p class="text-xs"><span id="seo-desc-count">{{ strlen($blog->seo_description ?? '') }}</span>/160</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Keywords</label>
                        <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $blog->seo_keywords) }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="keyword1, keyword2, keyword3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
                        <input type="url" name="canonical_url" value="{{ old('canonical_url', $blog->canonical_url) }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="https://example.com/blog/post-slug">
                    </div>

                    <div class="flex space-x-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="robots_index" value="1" {{ $blog->robots_index ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Allow indexing</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="robots_follow" value="1" {{ $blog->robots_follow ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Allow following links</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Open Graph -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fab fa-facebook text-blue-600 mr-2"></i>Open Graph (Social Sharing)
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OG Title</label>
                        <input type="text" name="og_title" value="{{ old('og_title', $blog->og_title) }}" maxlength="70"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OG Description</label>
                        <textarea name="og_description" rows="2" maxlength="200"
                                  class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('og_description', $blog->og_description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OG Image</label>
                        @if($blog->og_image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $blog->og_image) }}" alt="OG Image" class="w-32 h-20 object-cover rounded">
                        </div>
                        @endif
                        <input type="file" name="og_image" accept="image/*"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>

            <!-- Twitter Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fab fa-twitter text-blue-400 mr-2"></i>Twitter Card
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Card Type</label>
                        <select name="twitter_card" class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="summary_large_image" {{ $blog->twitter_card === 'summary_large_image' ? 'selected' : '' }}>Summary Large Image</option>
                            <option value="summary" {{ $blog->twitter_card === 'summary' ? 'selected' : '' }}>Summary</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Twitter Title</label>
                        <input type="text" name="twitter_title" value="{{ old('twitter_title', $blog->twitter_title) }}" maxlength="70"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Twitter Description</label>
                        <textarea name="twitter_description" rows="2" maxlength="200"
                                  class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('twitter_description', $blog->twitter_description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Twitter Image</label>
                        @if($blog->twitter_image)
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $blog->twitter_image) }}" alt="Twitter Image" class="w-32 h-20 object-cover rounded">
                        </div>
                        @endif
                        <input type="file" name="twitter_image" accept="image/*"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Publish Settings -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Publish</h3>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_published" value="1" {{ $blog->is_published ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Published</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1" {{ $blog->is_featured ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Featured post</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                        <input type="datetime-local" name="published_at"
                               value="{{ $blog->published_at ? $blog->published_at->format('Y-m-d\TH:i') : '' }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="text-sm text-gray-500">
                        <p><i class="fas fa-eye mr-1"></i>Views: {{ number_format($blog->view_count) }}</p>
                        <p><i class="fas fa-clock mr-1"></i>Reading time: {{ $blog->reading_time }} min</p>
                    </div>

                    <div class="pt-4 border-t">
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-save mr-2"></i>Update Blog
                        </button>
                    </div>
                </div>
            </div>

            <!-- Category & Tags -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Category & Tags</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select name="category_id" class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $blog->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                        <div class="space-y-2 max-h-40 overflow-y-auto border rounded-lg p-3">
                            @foreach($tags as $tag)
                            <label class="flex items-center">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                       {{ $blog->tags->contains($tag->id) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">{{ $tag->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Featured Image -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Featured Image</h3>

                <div class="space-y-4">
                    @if($blog->featured_image)
                    <div>
                        <img src="{{ asset('storage/' . $blog->featured_image) }}" alt="{{ $blog->featured_image_alt }}"
                             class="w-full rounded-lg mb-2">
                        <label class="flex items-center text-sm text-red-600">
                            <input type="checkbox" name="remove_featured_image" value="1"
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2">Remove featured image</span>
                        </label>
                    </div>
                    @endif

                    <div>
                        <input type="file" name="featured_image" accept="image/*" id="featured-image-input"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <div id="featured-image-preview" class="mt-4 hidden">
                            <img src="" alt="Preview" class="w-full rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alt Text</label>
                        <input type="text" name="featured_image_alt" value="{{ old('featured_image_alt', $blog->featured_image_alt) }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Image description for SEO">
                    </div>
                </div>
            </div>

            <!-- Author -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Author</h3>

                <div class="space-y-4">
                    @if($blog->author_image)
                    <div class="flex items-center">
                        <img src="{{ asset('storage/' . $blog->author_image) }}" alt="{{ $blog->author_name }}"
                             class="w-12 h-12 rounded-full object-cover mr-3">
                        <span class="font-medium">{{ $blog->author_name }}</span>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Author Name</label>
                        <input type="text" name="author_name" value="{{ old('author_name', $blog->author_name) }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Author Bio</label>
                        <textarea name="author_bio" rows="2"
                                  class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">{{ old('author_bio', $blog->author_bio) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Author Image</label>
                        <input type="file" name="author_image" accept="image/*"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
$(document).ready(function() {
    $('#content').summernote({
        height: 400,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    // Character counters
    $('#seo-title').on('input', function() {
        $('#seo-title-count').text($(this).val().length);
    });

    $('#seo-description').on('input', function() {
        $('#seo-desc-count').text($(this).val().length);
    });

    // Featured image preview
    $('#featured-image-input').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#featured-image-preview img').attr('src', e.target.result);
                $('#featured-image-preview').removeClass('hidden');
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endsection
