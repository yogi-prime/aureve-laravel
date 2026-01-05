<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SizeGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SizeGuideController extends Controller
{
    /**
     * Display all size guides
     */
    public function index()
    {
        $sizeGuides = SizeGuide::withCount('products')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(10);

        return view('admin.size-guides.index', compact('sizeGuides'));
    }

    /**
     * Show the form for creating a new size guide
     */
    public function create()
    {
        return view('admin.size-guides.create');
    }

    /**
     * Store a newly created size guide
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $data = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active'),
                'sort_order' => $validated['sort_order'] ?? 0,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'size-guide-' . time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('size-guides', $imageName, 'public');
                $data['image_path'] = $imagePath;
            }

            SizeGuide::create($data);

            return redirect()->route('admin.size-guides.index')
                ->with('success', 'Size guide created successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating size guide: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the size guide
     */
    public function edit(SizeGuide $sizeGuide)
    {
        return view('admin.size-guides.edit', compact('sizeGuide'));
    }

    /**
     * Update the specified size guide
     */
    public function update(Request $request, SizeGuide $sizeGuide)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $data = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->has('is_active'),
                'sort_order' => $validated['sort_order'] ?? 0,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($sizeGuide->image_path) {
                    Storage::disk('public')->delete($sizeGuide->image_path);
                }

                $image = $request->file('image');
                $imageName = 'size-guide-' . time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('size-guides', $imageName, 'public');
                $data['image_path'] = $imagePath;
            }

            $sizeGuide->update($data);

            return redirect()->route('admin.size-guides.index')
                ->with('success', 'Size guide updated successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating size guide: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified size guide
     */
    public function destroy(SizeGuide $sizeGuide)
    {
        try {
            // Delete image if exists
            if ($sizeGuide->image_path) {
                Storage::disk('public')->delete($sizeGuide->image_path);
            }

            $sizeGuide->delete();

            return redirect()->route('admin.size-guides.index')
                ->with('success', 'Size guide deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting size guide: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus(SizeGuide $sizeGuide)
    {
        $sizeGuide->update(['is_active' => !$sizeGuide->is_active]);

        return redirect()->back()
            ->with('success', 'Size guide status updated!');
    }
}
