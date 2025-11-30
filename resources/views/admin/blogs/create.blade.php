@extends('layouts.admin')

@section('title', 'Create New Blog')
@section('page-title', 'Create New Blog')
@section('page-description', 'Add a new blog post with SEO optimization')

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
@endsection

@section('content')
<form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Basic Information</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Enter blog title" id="blog-title">
                        @error('title')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug') }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="auto-generated-from-title" id="blog-slug">
                        <p class="text-gray-500 text-xs mt-1">Leave empty to auto-generate from title</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Excerpt</label>
                        <textarea name="excerpt" rows="3"
                                  class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Brief summary of the blog post (max 500 characters)" maxlength="500">{{ old('excerpt') }}</textarea>
                        @error('excerpt')<p class="text-red-500 text-sm mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Content *</label>
                        <textarea name="content" id="content" rows="15"
                                  class="w-full border rounded-lg px-3 py-2">{{ old('content') }}</textarea>
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
                        <input type="text" name="seo_title" value="{{ old('seo_title') }}" maxlength="70"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="SEO optimized title (50-60 characters ideal)" id="seo-title">
                        <div class="flex justify-between mt-1">
                            <p class="text-gray-500 text-xs">Leave empty to use blog title</p>
                            <p class="text-xs"><span id="seo-title-count">0</span>/70</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Description</label>
                        <textarea name="seo_description" rows="2" maxlength="160"
                                  class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Meta description for search engines (120-160 characters ideal)" id="seo-description">{{ old('seo_description') }}</textarea>
                        <div class="flex justify-between mt-1">
                            <p class="text-gray-500 text-xs">Leave empty to auto-generate from excerpt</p>
                            <p class="text-xs"><span id="seo-desc-count">0</span>/160</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SEO Keywords</label>
                        <input type="text" name="seo_keywords" value="{{ old('seo_keywords') }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="keyword1, keyword2, keyword3">
                        <p class="text-gray-500 text-xs mt-1">Separate keywords with commas</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
                        <input type="url" name="canonical_url" value="{{ old('canonical_url') }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="https://example.com/blog/post-slug">
                    </div>

                    <div class="flex space-x-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="robots_index" value="1" checked
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Allow indexing</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" name="robots_follow" value="1" checked
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
                        <input type="text" name="og_title" value="{{ old('og_title') }}" maxlength="70"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Leave empty to use SEO title">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OG Description</label>
                        <textarea name="og_description" rows="2" maxlength="200"
                                  class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Leave empty to use SEO description">{{ old('og_description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">OG Image (1200x630 recommended)</label>
                        <input type="file" name="og_image" accept="image/*"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-gray-500 text-xs mt-1">Leave empty to use featured image</p>
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
                            <option value="summary_large_image" selected>Summary Large Image</option>
                            <option value="summary">Summary</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Twitter Title</label>
                        <input type="text" name="twitter_title" value="{{ old('twitter_title') }}" maxlength="70"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Leave empty to use SEO title">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Twitter Description</label>
                        <textarea name="twitter_description" rows="2" maxlength="200"
                                  class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Leave empty to use SEO description">{{ old('twitter_description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Twitter Image</label>
                        <input type="file" name="twitter_image" accept="image/*"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-gray-500 text-xs mt-1">Leave empty to use OG image</p>
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
                            <input type="checkbox" name="is_published" value="1"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Publish immediately</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" value="1"
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Featured post</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Publish Date</label>
                        <input type="datetime-local" name="published_at"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div class="pt-4 border-t">
                        <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-save mr-2"></i>Save Blog
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
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
                        <div class="space-y-2 max-h-40 overflow-y-auto border rounded-lg p-3">
                            @foreach($tags as $tag)
                            <label class="flex items-center">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
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
                    <div>
                        <input type="file" name="featured_image" accept="image/*" id="featured-image-input"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <div id="featured-image-preview" class="mt-4 hidden">
                            <img src="" alt="Preview" class="w-full rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alt Text</label>
                        <input type="text" name="featured_image_alt" value="{{ old('featured_image_alt') }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="Image description for SEO">
                    </div>
                </div>
            </div>

            <!-- Author -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Author</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Author Name</label>
                        <input type="text" name="author_name" value="{{ old('author_name', 'Aureve Team') }}"
                               class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Author Bio</label>
                        <textarea name="author_bio" rows="2"
                                  class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Short author bio">{{ old('author_bio') }}</textarea>
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
    // Initialize Summernote editor
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

    // Auto-generate slug from title
    $('#blog-title').on('input', function() {
        const title = $(this).val();
        const slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
        $('#blog-slug').attr('placeholder', slug);
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
