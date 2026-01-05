<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CollectionController extends Controller
{
    /**
     * Display all collections
     */
    public function index()
    {
        $collections = Collection::with(['parent', 'children'])
            ->withCount('products')
            ->latest()
            ->paginate(15);

        return view('admin.collections.index', compact('collections'));
    }

    /**
     * Show the form for creating a new collection
     */
    public function create()
    {
        $parentCollections = Collection::whereNull('parent_id')
            ->where('is_active', true)
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);

        return view('admin.collections.create', compact('parentCollections', 'products'));
    }

    /**
     * Store a newly created collection
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:collections,name',
            'parent_id' => 'nullable|exists:collections,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'boolean',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        try {
            // Handle image upload
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('collections', 'public');
            }

            // Create collection
            $collection = Collection::create([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'parent_id' => $validated['parent_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'image' => $imagePath,
                'sort_order' => $validated['sort_order'] ?? 0,
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'meta_keywords' => $validated['meta_keywords'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            // Attach products if selected
            if (!empty($validated['products'])) {
                $collection->products()->attach($validated['products']);
            }

            return redirect()->route('admin.collections.index')
                ->with('success', 'Collection created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating collection: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the collection
     */
    public function edit(Collection $collection)
    {
        $parentCollections = Collection::whereNull('parent_id')
            ->where('id', '!=', $collection->id)
            ->where('is_active', true)
            ->get();

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku']);

        $selectedProducts = $collection->products()->pluck('products.id')->toArray();

        return view('admin.collections.edit', compact('collection', 'parentCollections', 'products', 'selectedProducts'));
    }

    /**
     * Update the specified collection
     */
    public function update(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:collections,name,' . $collection->id,
            'parent_id' => 'nullable|exists:collections,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'sort_order' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'boolean',
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        try {
            // Handle image upload
            $imagePath = $collection->image;
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($collection->image) {
                    Storage::disk('public')->delete($collection->image);
                }
                $imagePath = $request->file('image')->store('collections', 'public');
            }

            // Update collection
            $collection->update([
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name']),
                'parent_id' => $validated['parent_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'image' => $imagePath,
                'sort_order' => $validated['sort_order'] ?? 0,
                'meta_title' => $validated['meta_title'] ?? null,
                'meta_description' => $validated['meta_description'] ?? null,
                'meta_keywords' => $validated['meta_keywords'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            // Sync products
            $collection->products()->sync($validated['products'] ?? []);

            return redirect()->route('admin.collections.index')
                ->with('success', 'Collection updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating collection: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified collection
     */
    public function destroy(Collection $collection)
    {
        try {
            // Check if collection has children
            if ($collection->children()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete collection that has sub-collections. Please delete sub-collections first.');
            }

            // Delete image if exists
            if ($collection->image) {
                Storage::disk('public')->delete($collection->image);
            }

            // Detach all products (pivot table will handle this with cascade)
            $collection->products()->detach();

            $collection->delete();

            return redirect()->route('admin.collections.index')
                ->with('success', 'Collection deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting collection: ' . $e->getMessage());
        }
    }

    /**
     * Toggle collection status
     */
    public function toggleStatus(Collection $collection)
    {
        try {
            $collection->update([
                'is_active' => !$collection->is_active
            ]);

            $status = $collection->is_active ? 'activated' : 'deactivated';

            return redirect()->back()
                ->with('success', "Collection {$status} successfully!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating collection status: ' . $e->getMessage());
        }
    }

    /**
     * Manage products in collection
     */
    public function manageProducts(Collection $collection)
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'base_price']);

        $selectedProducts = $collection->products()->pluck('products.id')->toArray();

        return view('admin.collections.manage-products', compact('collection', 'products', 'selectedProducts'));
    }

    /**
     * Update products in collection
     */
    public function updateProducts(Request $request, Collection $collection)
    {
        $validated = $request->validate([
            'products' => 'nullable|array',
            'products.*' => 'exists:products,id',
        ]);

        try {
            $collection->products()->sync($validated['products'] ?? []);

            return redirect()->route('admin.collections.index')
                ->with('success', 'Collection products updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating collection products: ' . $e->getMessage());
        }
    }
}
