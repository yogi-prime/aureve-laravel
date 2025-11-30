<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with(['category', 'tags'])
            ->latest()
            ->paginate(10);

        return view('admin.blogs.index', compact('blogs'));
    }

    public function create()
    {
        $categories = BlogCategory::where('is_active', true)->get();
        $tags = BlogTag::where('is_active', true)->get();

        return view('admin.blogs.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:blogs,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:blog_categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:blog_tags,id',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'author_name' => 'nullable|string|max:255',
            'author_bio' => 'nullable|string',
            'author_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image_alt' => 'nullable|string|max:255',
            'seo_title' => 'nullable|string|max:70',
            'seo_description' => 'nullable|string|max:160',
            'seo_keywords' => 'nullable|string',
            'canonical_url' => 'nullable|url',
            'og_title' => 'nullable|string|max:70',
            'og_description' => 'nullable|string|max:200',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'og_type' => 'nullable|string',
            'twitter_card' => 'nullable|string',
            'twitter_title' => 'nullable|string|max:70',
            'twitter_description' => 'nullable|string|max:200',
            'twitter_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'schema_markup' => 'nullable|json',
        ]);

        try {
            $featuredImagePath = null;
            if ($request->hasFile('featured_image')) {
                $featuredImagePath = $request->file('featured_image')->store('blogs', 'public');
            }

            $authorImagePath = null;
            if ($request->hasFile('author_image')) {
                $authorImagePath = $request->file('author_image')->store('blogs/authors', 'public');
            }

            $ogImagePath = null;
            if ($request->hasFile('og_image')) {
                $ogImagePath = $request->file('og_image')->store('blogs/og', 'public');
            }

            $twitterImagePath = null;
            if ($request->hasFile('twitter_image')) {
                $twitterImagePath = $request->file('twitter_image')->store('blogs/twitter', 'public');
            }

            // Auto-generate SEO from title/content - limit to proper lengths
            $seoTitle = !empty($validated['seo_title']) ? $validated['seo_title'] : Str::limit($validated['title'], 60, '');

            // Auto-generate description from excerpt or content - limit to 155 chars
            if (!empty($validated['seo_description'])) {
                $seoDescription = $validated['seo_description'];
            } elseif (!empty($validated['excerpt'])) {
                $seoDescription = Str::limit($validated['excerpt'], 155, '');
            } else {
                $seoDescription = Str::limit(strip_tags($validated['content']), 155, '');
            }

            // Auto-generate keywords from content
            $seoKeywords = !empty($validated['seo_keywords']) ? $validated['seo_keywords'] : $this->extractKeywords($validated['content'], $validated['title']);

            $blog = Blog::create([
                'title' => $validated['title'],
                'slug' => $validated['slug'] ?? Str::slug($validated['title']),
                'excerpt' => $validated['excerpt'] ?? null,
                'content' => $validated['content'],
                'featured_image' => $featuredImagePath,
                'featured_image_alt' => $validated['featured_image_alt'] ?? $validated['title'],
                'category_id' => $validated['category_id'] ?? null,
                'is_published' => $request->has('is_published'),
                'is_featured' => $request->has('is_featured'),
                'published_at' => $request->has('is_published') ? ($validated['published_at'] ?? now()) : null,
                'author_name' => $validated['author_name'] ?? 'Aureve Team',
                'author_image' => $authorImagePath,
                'author_bio' => $validated['author_bio'] ?? null,
                'seo_title' => $seoTitle,
                'seo_description' => $seoDescription,
                'seo_keywords' => $seoKeywords,
                'canonical_url' => $validated['canonical_url'] ?? null,
                'og_title' => $validated['og_title'] ?? $seoTitle,
                'og_description' => $validated['og_description'] ?? $seoDescription,
                'og_image' => $ogImagePath ?? $featuredImagePath,
                'og_type' => $validated['og_type'] ?? 'article',
                'twitter_card' => $validated['twitter_card'] ?? 'summary_large_image',
                'twitter_title' => $validated['twitter_title'] ?? $seoTitle,
                'twitter_description' => $validated['twitter_description'] ?? $seoDescription,
                'twitter_image' => $twitterImagePath ?? $ogImagePath ?? $featuredImagePath,
                'robots_index' => $request->has('robots_index') || !$request->filled('robots_index'),
                'robots_follow' => $request->has('robots_follow') || !$request->filled('robots_follow'),
                'schema_markup' => isset($validated['schema_markup']) ? json_decode($validated['schema_markup'], true) : null,
            ]);

            if (isset($validated['tags'])) {
                $blog->tags()->sync($validated['tags']);
            }

            return redirect()->route('admin.blogs.index')
                ->with('success', 'Blog created successfully!');

        } catch (\Exception $e) {
            \Log::error('Error creating blog', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Error creating blog: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Blog $blog)
    {
        $blog->load(['category', 'tags']);
        $seoScore = $this->calculateSeoScore($blog);
        return view('admin.blogs.show', compact('blog', 'seoScore'));
    }

    public function edit(Blog $blog)
    {
        $blog->load(['category', 'tags']);
        $categories = BlogCategory::where('is_active', true)->get();
        $tags = BlogTag::where('is_active', true)->get();
        $seoScore = $this->calculateSeoScore($blog);
        return view('admin.blogs.edit', compact('blog', 'categories', 'tags', 'seoScore'));
    }

    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:blogs,slug,' . $blog->id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category_id' => 'nullable|exists:blog_categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:blog_tags,id',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'nullable|date',
            'author_name' => 'nullable|string|max:255',
            'author_bio' => 'nullable|string',
            'author_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image_alt' => 'nullable|string|max:255',
            'remove_featured_image' => 'boolean',
            'seo_title' => 'nullable|string|max:70',
            'seo_description' => 'nullable|string|max:160',
            'seo_keywords' => 'nullable|string',
            'canonical_url' => 'nullable|url',
            'og_title' => 'nullable|string|max:70',
            'og_description' => 'nullable|string|max:200',
            'og_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'og_type' => 'nullable|string',
            'twitter_card' => 'nullable|string',
            'twitter_title' => 'nullable|string|max:70',
            'twitter_description' => 'nullable|string|max:200',
            'twitter_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'robots_index' => 'boolean',
            'robots_follow' => 'boolean',
            'schema_markup' => 'nullable|json',
        ]);

        try {
            \DB::beginTransaction();

            $featuredImagePath = $blog->featured_image;
            if ($request->has('remove_featured_image') && $blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
                $featuredImagePath = null;
            }
            if ($request->hasFile('featured_image')) {
                if ($blog->featured_image) {
                    Storage::disk('public')->delete($blog->featured_image);
                }
                $featuredImagePath = $request->file('featured_image')->store('blogs', 'public');
            }

            $authorImagePath = $blog->author_image;
            if ($request->hasFile('author_image')) {
                if ($blog->author_image) {
                    Storage::disk('public')->delete($blog->author_image);
                }
                $authorImagePath = $request->file('author_image')->store('blogs/authors', 'public');
            }

            $ogImagePath = $blog->og_image;
            if ($request->hasFile('og_image')) {
                if ($blog->og_image && $blog->og_image !== $blog->featured_image) {
                    Storage::disk('public')->delete($blog->og_image);
                }
                $ogImagePath = $request->file('og_image')->store('blogs/og', 'public');
            }

            $twitterImagePath = $blog->twitter_image;
            if ($request->hasFile('twitter_image')) {
                if ($blog->twitter_image && $blog->twitter_image !== $blog->featured_image && $blog->twitter_image !== $blog->og_image) {
                    Storage::disk('public')->delete($blog->twitter_image);
                }
                $twitterImagePath = $request->file('twitter_image')->store('blogs/twitter', 'public');
            }

            // Auto-generate SEO from title/content - limit to proper lengths
            $seoTitle = !empty($validated['seo_title']) ? $validated['seo_title'] : Str::limit($validated['title'], 60, '');

            // Auto-generate description from excerpt or content - limit to 155 chars
            if (!empty($validated['seo_description'])) {
                $seoDescription = $validated['seo_description'];
            } elseif (!empty($validated['excerpt'])) {
                $seoDescription = Str::limit($validated['excerpt'], 155, '');
            } else {
                $seoDescription = Str::limit(strip_tags($validated['content']), 155, '');
            }

            // Auto-generate keywords from content
            $seoKeywords = !empty($validated['seo_keywords']) ? $validated['seo_keywords'] : $this->extractKeywords($validated['content'], $validated['title']);

            $blog->update([
                'title' => $validated['title'],
                'slug' => $validated['slug'] ?? Str::slug($validated['title']),
                'excerpt' => $validated['excerpt'] ?? null,
                'content' => $validated['content'],
                'featured_image' => $featuredImagePath,
                'featured_image_alt' => $validated['featured_image_alt'] ?? $validated['title'],
                'category_id' => $validated['category_id'] ?? null,
                'is_published' => $request->has('is_published'),
                'is_featured' => $request->has('is_featured'),
                'published_at' => $request->has('is_published') ? ($validated['published_at'] ?? $blog->published_at ?? now()) : null,
                'author_name' => $validated['author_name'] ?? $blog->author_name,
                'author_image' => $authorImagePath,
                'author_bio' => $validated['author_bio'] ?? null,
                'seo_title' => $seoTitle,
                'seo_description' => $seoDescription,
                'seo_keywords' => $seoKeywords,
                'canonical_url' => $validated['canonical_url'] ?? null,
                'og_title' => $validated['og_title'] ?? $seoTitle,
                'og_description' => $validated['og_description'] ?? $seoDescription,
                'og_image' => $ogImagePath ?? $featuredImagePath,
                'og_type' => $validated['og_type'] ?? 'article',
                'twitter_card' => $validated['twitter_card'] ?? 'summary_large_image',
                'twitter_title' => $validated['twitter_title'] ?? $seoTitle,
                'twitter_description' => $validated['twitter_description'] ?? $seoDescription,
                'twitter_image' => $twitterImagePath ?? $ogImagePath ?? $featuredImagePath,
                'robots_index' => $request->has('robots_index'),
                'robots_follow' => $request->has('robots_follow'),
                'schema_markup' => isset($validated['schema_markup']) ? json_decode($validated['schema_markup'], true) : $blog->schema_markup,
            ]);

            $blog->tags()->sync($validated['tags'] ?? []);

            \DB::commit();

            return redirect()->route('admin.blogs.index')
                ->with('success', 'Blog updated successfully!');

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error updating blog', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->with('error', 'Error updating blog: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Blog $blog)
    {
        try {
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            if ($blog->author_image) {
                Storage::disk('public')->delete($blog->author_image);
            }
            if ($blog->og_image && $blog->og_image !== $blog->featured_image) {
                Storage::disk('public')->delete($blog->og_image);
            }
            if ($blog->twitter_image && $blog->twitter_image !== $blog->featured_image && $blog->twitter_image !== $blog->og_image) {
                Storage::disk('public')->delete($blog->twitter_image);
            }

            $blog->delete();

            return redirect()->route('admin.blogs.index')
                ->with('success', 'Blog deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting blog: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Blog $blog)
    {
        $blog->update([
            'is_published' => !$blog->is_published,
            'published_at' => !$blog->is_published ? now() : $blog->published_at,
        ]);

        return redirect()->back()
            ->with('success', 'Blog status updated successfully!');
    }

    public function toggleFeatured(Blog $blog)
    {
        $blog->update(['is_featured' => !$blog->is_featured]);

        return redirect()->back()
            ->with('success', 'Blog featured status updated successfully!');
    }

    public function calculateSeoScore(Blog $blog): array
    {
        $score = 0;
        $maxScore = 100;
        $feedback = [];

        if ($blog->seo_title) {
            $titleLength = strlen($blog->seo_title);
            if ($titleLength >= 50 && $titleLength <= 60) {
                $score += 15;
                $feedback[] = ['type' => 'success', 'message' => 'SEO title length is perfect (50-60 characters)'];
            } elseif ($titleLength >= 30 && $titleLength <= 70) {
                $score += 10;
                $feedback[] = ['type' => 'warning', 'message' => 'SEO title length is acceptable, but 50-60 characters is ideal'];
            } else {
                $score += 5;
                $feedback[] = ['type' => 'error', 'message' => 'SEO title should be 50-60 characters'];
            }
        } else {
            $feedback[] = ['type' => 'error', 'message' => 'SEO title is missing'];
        }

        if ($blog->seo_description) {
            $descLength = strlen($blog->seo_description);
            if ($descLength >= 120 && $descLength <= 160) {
                $score += 15;
                $feedback[] = ['type' => 'success', 'message' => 'SEO description length is perfect (120-160 characters)'];
            } elseif ($descLength >= 80 && $descLength <= 200) {
                $score += 10;
                $feedback[] = ['type' => 'warning', 'message' => 'SEO description length is acceptable, but 120-160 characters is ideal'];
            } else {
                $score += 5;
                $feedback[] = ['type' => 'error', 'message' => 'SEO description should be 120-160 characters'];
            }
        } else {
            $feedback[] = ['type' => 'error', 'message' => 'SEO description is missing'];
        }

        if ($blog->seo_keywords) {
            $score += 10;
            $feedback[] = ['type' => 'success', 'message' => 'SEO keywords are set'];
        } else {
            $feedback[] = ['type' => 'warning', 'message' => 'Consider adding SEO keywords'];
        }

        if ($blog->featured_image) {
            $score += 10;
            $feedback[] = ['type' => 'success', 'message' => 'Featured image is set'];
        } else {
            $feedback[] = ['type' => 'error', 'message' => 'Featured image is missing'];
        }

        if ($blog->featured_image_alt) {
            $score += 5;
            $feedback[] = ['type' => 'success', 'message' => 'Featured image has alt text'];
        } else if ($blog->featured_image) {
            $feedback[] = ['type' => 'warning', 'message' => 'Add alt text to featured image'];
        }

        $wordCount = str_word_count(strip_tags($blog->content));
        if ($wordCount >= 1000) {
            $score += 15;
            $feedback[] = ['type' => 'success', 'message' => "Content is comprehensive ({$wordCount} words)"];
        } elseif ($wordCount >= 500) {
            $score += 10;
            $feedback[] = ['type' => 'warning', 'message' => "Content could be more detailed ({$wordCount} words). Aim for 1000+ words"];
        } else {
            $score += 5;
            $feedback[] = ['type' => 'error', 'message' => "Content is too short ({$wordCount} words). Aim for at least 500 words"];
        }

        if ($blog->excerpt) {
            $score += 5;
            $feedback[] = ['type' => 'success', 'message' => 'Excerpt is set'];
        } else {
            $feedback[] = ['type' => 'warning', 'message' => 'Consider adding an excerpt'];
        }

        if ($blog->og_title && $blog->og_description && $blog->og_image) {
            $score += 10;
            $feedback[] = ['type' => 'success', 'message' => 'Open Graph data is complete'];
        } elseif ($blog->og_title || $blog->og_description) {
            $score += 5;
            $feedback[] = ['type' => 'warning', 'message' => 'Open Graph data is partial - complete all fields'];
        } else {
            $feedback[] = ['type' => 'warning', 'message' => 'Consider adding Open Graph data for social sharing'];
        }

        if ($blog->twitter_title && $blog->twitter_description) {
            $score += 10;
            $feedback[] = ['type' => 'success', 'message' => 'Twitter Card data is set'];
        } else {
            $feedback[] = ['type' => 'warning', 'message' => 'Consider adding Twitter Card data'];
        }

        if ($blog->category_id) {
            $score += 5;
            $feedback[] = ['type' => 'success', 'message' => 'Blog has a category'];
        } else {
            $feedback[] = ['type' => 'warning', 'message' => 'Consider assigning a category'];
        }

        $grade = match(true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };

        return [
            'score' => $score,
            'percentage' => round(($score / $maxScore) * 100),
            'feedback' => $feedback,
            'max_score' => $maxScore,
            'grade' => $grade,
        ];
    }

    /**
     * Extract keywords from content automatically
     */
    private function extractKeywords(string $content, string $title): string
    {
        // Remove HTML tags
        $text = strip_tags($content) . ' ' . $title;

        // Convert to lowercase
        $text = strtolower($text);

        // Remove special characters and numbers
        $text = preg_replace('/[^a-z\s]/', '', $text);

        // Split into words
        $words = str_word_count($text, 1);

        // Common stop words to exclude
        $stopWords = [
            'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for',
            'of', 'with', 'by', 'from', 'as', 'is', 'was', 'are', 'were', 'been',
            'be', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would',
            'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that',
            'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they',
            'what', 'which', 'who', 'when', 'where', 'why', 'how', 'all',
            'each', 'every', 'both', 'few', 'more', 'most', 'other', 'some',
            'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'than',
            'too', 'very', 'just', 'also', 'now', 'here', 'there', 'then',
            'if', 'else', 'our', 'your', 'its', 'their', 'my', 'about', 'into',
            'through', 'during', 'before', 'after', 'above', 'below', 'up', 'down',
            'out', 'off', 'over', 'under', 'again', 'further', 'once', 'being',
            'having', 'doing', 'while', 'however', 'because', 'although'
        ];

        // Filter out stop words and short words
        $filteredWords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 3 && !in_array($word, $stopWords);
        });

        // Count word frequency
        $wordCounts = array_count_values($filteredWords);

        // Sort by frequency
        arsort($wordCounts);

        // Get top 8-10 keywords
        $keywords = array_slice(array_keys($wordCounts), 0, 10);

        return implode(', ', $keywords);
    }
}
