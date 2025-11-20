<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display all categories
     */
    public function index()
    {
        $categories = Category::with(['parent', 'children'])
            ->withCount('products')
            ->latest()
            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('is_active', true)
            ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('categories', 'public');
            }

            // Create category
            Category::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'parent_id' => $validated['parent_id'],
                'description' => $validated['description'],
                'image' => $imagePath,
                'sort_order' => $validated['sort_order'] ?? 0,
                'meta_title' => $validated['meta_title'],
                'meta_description' => $validated['meta_description'],
                'meta_keywords' => $validated['meta_keywords'],
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the category
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->where('is_active', true)
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sort_order' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            // Handle image upload
            $imagePath = $category->image;
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($category->image) {
                    Storage::disk('public')->delete($category->image);
                }
                $imagePath = $request->file('image')->store('categories', 'public');
            }

            // Update category
            $category->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'parent_id' => $validated['parent_id'],
                'description' => $validated['description'],
                'image' => $imagePath,
                'sort_order' => $validated['sort_order'] ?? 0,
                'meta_title' => $validated['meta_title'],
                'meta_description' => $validated['meta_description'],
                'meta_keywords' => $validated['meta_keywords'],
                'is_active' => $request->has('is_active'),
            ]);

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        try {
            // Check if category has products
            if ($category->products()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete category that has products. Please reassign products first.');
            }

            // Check if category has children
            if ($category->children()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete category that has sub-categories. Please delete sub-categories first.');
            }

            // Delete image if exists
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }

            $category->delete();

            return redirect()->route('admin.categories.index')
                ->with('success', 'Category deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting category: ' . $e->getMessage());
        }
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(Category $category)
    {
        try {
            $category->update([
                'is_active' => !$category->is_active
            ]);

            $status = $category->is_active ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "Category {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating category status: ' . $e->getMessage());
        }
    }
}