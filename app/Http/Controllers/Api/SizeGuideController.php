<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SizeGuide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SizeGuideController extends Controller
{
    /**
     * Display a listing of all size guides
     */
    public function index(Request $request)
    {
        try {
            $query = SizeGuide::query();

            // Filter by active status
            if ($request->has('active_only') && $request->active_only) {
                $query->active();
            }

            // Order by sort_order and title
            $query->ordered();

            $sizeGuides = $query->get();

            return response()->json([
                'success' => true,
                'data' => $sizeGuides
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch size guides', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch size guides',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created size guide
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
                'is_active' => 'boolean',
                'sort_order' => 'integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = [
                'title' => $request->title,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
                'sort_order' => $request->sort_order ?? 0,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'size-guide-' . time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('size-guides', $imageName, 'public');
                $data['image_path'] = $imagePath;
            }

            $sizeGuide = SizeGuide::create($data);

            Log::info('Size guide created', ['id' => $sizeGuide->id, 'title' => $sizeGuide->title]);

            return response()->json([
                'success' => true,
                'message' => 'Size guide created successfully',
                'data' => $sizeGuide
            ], 201);

        } catch (\Exception $e) {
            Log::error('Failed to create size guide', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create size guide',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified size guide
     */
    public function show($id)
    {
        try {
            $sizeGuide = SizeGuide::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $sizeGuide
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Size guide not found'
            ], 404);
        }
    }

    /**
     * Update the specified size guide
     */
    public function update(Request $request, $id)
    {
        try {
            $sizeGuide = SizeGuide::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
                'is_active' => 'boolean',
                'sort_order' => 'integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only(['title', 'description', 'is_active', 'sort_order']);

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($sizeGuide->image_path) {
                    Storage::disk('public')->delete($sizeGuide->image_path);
                }

                $image = $request->file('image');
                $imageName = 'size-guide-' . time() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('size-guides', $imageName, 'public');
                $data['image_path'] = $imagePath;
            }

            $sizeGuide->update($data);

            Log::info('Size guide updated', ['id' => $sizeGuide->id]);

            return response()->json([
                'success' => true,
                'message' => 'Size guide updated successfully',
                'data' => $sizeGuide->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update size guide', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update size guide',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified size guide
     */
    public function destroy($id)
    {
        try {
            $sizeGuide = SizeGuide::findOrFail($id);

            // Delete image if exists
            if ($sizeGuide->image_path) {
                Storage::disk('public')->delete($sizeGuide->image_path);
            }

            $sizeGuide->delete();

            Log::info('Size guide deleted', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Size guide deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete size guide', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete size guide',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active size guides for dropdown selection
     */
    public function dropdown()
    {
        try {
            $sizeGuides = SizeGuide::active()
                ->ordered()
                ->select('id', 'title')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $sizeGuides
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch size guides'
            ], 500);
        }
    }
}
