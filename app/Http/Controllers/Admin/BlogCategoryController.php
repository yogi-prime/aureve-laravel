<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    /**
     * Display all blog categories
     */
    public function index()
    {
        $categories = BlogCategory::withCount('blogs')
            ->latest()
            ->paginate(15);

        return view('admin.blog-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new blog category
     */
    public function create()
    {
        return view('admin.blog-categories.create');
    }

    /**
     * Store a newly created blog category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
            'description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            BlogCategory::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'seo_title' => $validated['seo_title'] ?? null,
                'seo_description' => $validated['seo_description'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.blog-categories.index')
                ->with('success', 'Blog category created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating blog category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the blog category
     */
    public function edit(BlogCategory $blog_category)
    {
        return view('admin.blog-categories.edit', compact('blog_category'));
    }

    /**
     * Update the specified blog category
     */
    public function update(Request $request, BlogCategory $blog_category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name,' . $blog_category->id,
            'description' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $blog_category->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'seo_title' => $validated['seo_title'] ?? null,
                'seo_description' => $validated['seo_description'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.blog-categories.index')
                ->with('success', 'Blog category updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating blog category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified blog category
     */
    public function destroy(BlogCategory $blog_category)
    {
        try {
            // Check if category has blogs
            if ($blog_category->blogs()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete category that has blogs. Please reassign blogs first.');
            }

            $blog_category->delete();

            return redirect()->route('admin.blog-categories.index')
                ->with('success', 'Blog category deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting blog category: ' . $e->getMessage());
        }
    }

    /**
     * Toggle blog category status
     */
    public function toggleStatus(BlogCategory $blogCategory)
    {
        try {
            $blogCategory->update([
                'is_active' => !$blogCategory->is_active
            ]);

            $status = $blogCategory->is_active ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "Blog category {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating blog category status: ' . $e->getMessage());
        }
    }
}
