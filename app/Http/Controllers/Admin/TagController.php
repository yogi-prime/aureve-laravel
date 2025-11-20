<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Display all tags
     */
    public function index()
    {
        $tags = Tag::withCount('products')
            ->latest()
            ->paginate(15);

        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new tag
     */
    public function create()
    {
        return view('admin.tags.create');
    }

    /**
     * Store a newly created tag
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            Tag::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.tags.index')
                ->with('success', 'Tag created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating tag: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the tag
     */
    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    /**
     * Update the specified tag
     */
    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $tag->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'description' => $validated['description'],
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.tags.index')
                ->with('success', 'Tag updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating tag: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified tag
     */
    public function destroy(Tag $tag)
    {
        try {
            // Detach products from this tag
            $tag->products()->detach();
            
            $tag->delete();

            return redirect()->route('admin.tags.index')
                ->with('success', 'Tag deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting tag: ' . $e->getMessage());
        }
    }

    /**
     * Toggle tag status
     */
    public function toggleStatus(Tag $tag)
    {
        try {
            $tag->update([
                'is_active' => !$tag->is_active
            ]);

            $status = $tag->is_active ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "Tag {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating tag status: ' . $e->getMessage());
        }
    }
}