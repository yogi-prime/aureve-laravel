<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogTagController extends Controller
{
    /**
     * Display all blog tags
     */
    public function index()
    {
        $tags = BlogTag::withCount('blogs')
            ->latest()
            ->paginate(15);

        return view('admin.blog-tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new blog tag
     */
    public function create()
    {
        return view('admin.blog-tags.create');
    }

    /**
     * Store a newly created blog tag
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_tags,name',
            'is_active' => 'boolean',
        ]);

        try {
            BlogTag::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.blog-tags.index')
                ->with('success', 'Blog tag created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating blog tag: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the blog tag
     */
    public function edit(BlogTag $blog_tag)
    {
        return view('admin.blog-tags.edit', compact('blog_tag'));
    }

    /**
     * Update the specified blog tag
     */
    public function update(Request $request, BlogTag $blog_tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:blog_tags,name,' . $blog_tag->id,
            'is_active' => 'boolean',
        ]);

        try {
            $blog_tag->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.blog-tags.index')
                ->with('success', 'Blog tag updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating blog tag: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified blog tag
     */
    public function destroy(BlogTag $blog_tag)
    {
        try {
            // Detach blogs from this tag
            $blog_tag->blogs()->detach();

            $blog_tag->delete();

            return redirect()->route('admin.blog-tags.index')
                ->with('success', 'Blog tag deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting blog tag: ' . $e->getMessage());
        }
    }

    /**
     * Toggle blog tag status
     */
    public function toggleStatus(BlogTag $blogTag)
    {
        try {
            $blogTag->update([
                'is_active' => !$blogTag->is_active
            ]);

            $status = $blogTag->is_active ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "Blog tag {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating blog tag status: ' . $e->getMessage());
        }
    }
}
