<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use App\Models\ProductVariant;
use App\Models\ProductImage;
use App\Models\ProductSeo;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display all products in admin panel
     */
    public function index()
    {
        $products = Product::with(['category', 'variants', 'images', 'seo'])
            ->latest()
            ->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        $tags = Tag::where('is_active', true)->get();
        
        return view('admin.products.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created product
     */
   public function store(Request $request)
{
    // Step 1: Validation
    \Log::info('🟢 [ProductController@store] Validation started', ['input' => $request->all()]);

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'short_description' => 'nullable|string',
        'description' => 'required|string',
        'base_price' => 'required|numeric|min:0',
        'sale_price' => 'nullable|numeric|min:0',
        'sku' => 'required|string|unique:products,sku',
        'stock_quantity' => 'required|integer|min:0',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'tags' => 'array',
        'tags.*' => 'exists:tags,id',

        // Variants
        'variants' => 'array',
        'variants.*.color_name' => 'required|string',
        'variants.*.color_code' => 'nullable|string',
        'variants.*.size' => 'nullable|string',
        'variants.*.material' => 'nullable|string',
        'variants.*.price' => 'required|numeric|min:0',
        'variants.*.sale_price' => 'nullable|numeric|min:0',
        'variants.*.stock_quantity' => 'required|integer|min:0',
        'variants.*.sku' => 'required|string',
        'variants.*.weight' => 'nullable|string',
        'variants.*.variant_description' => 'nullable|string',

        // SEO
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string',
        'meta_keywords' => 'nullable|string',
        'focus_keyword' => 'nullable|string',
        'seo_content' => 'nullable|string',

        // Images
        'images' => 'array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    try {
        // Step 2: Create product
        \Log::info('🟢 Creating product', ['data' => $validated]);

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category_id' => $validated['category_id'],
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'sku' => $validated['sku'],
            'stock_quantity' => $validated['stock_quantity'],
            'in_stock' => $validated['stock_quantity'] > 0,
            'brand' => $validated['brand'] ?? null,
            'model' => $validated['model'] ?? null,
            'is_featured' => $request->has('is_featured'),
            'is_active' => $request->has('is_active'),
        ]);

        \Log::info('✅ Product created', ['product_id' => $product->id]);

        // Step 3: Attach tags
        if (isset($validated['tags'])) {
            $product->tags()->sync($validated['tags']);
            \Log::info('✅ Tags attached', ['tags' => $validated['tags']]);
        }

        // Step 4: Create variants
        if (isset($validated['variants'])) {
            \Log::info('🟢 Creating variants', ['count' => count($validated['variants'])]);
            foreach ($validated['variants'] as $variantData) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'color_name' => $variantData['color_name'],
                    'color_code' => $variantData['color_code'] ?? null,
                    'size' => $variantData['size'] ?? null,
                    'material' => $variantData['material'] ?? null,
                    'price' => $variantData['price'],
                    'sale_price' => $variantData['sale_price'] ?? null,
                    'stock_quantity' => $variantData['stock_quantity'],
                    'sku' => $variantData['sku'],
                    'weight' => $variantData['weight'] ?? null,
                    'variant_description' => $variantData['variant_description'] ?? null,
                ]);
                \Log::info('✅ Variant created', ['sku' => $variantData['sku']]);
            }
        }

        // Step 5: Create SEO data
        \Log::info('🟢 Creating SEO data', [
            'meta_title' => $validated['meta_title'] ?? $validated['name'],
            'meta_description' => $validated['meta_description'] ?? $validated['short_description'],
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'focus_keyword' => $validated['focus_keyword'] ?? null,
        ]);

        ProductSeo::create([
            'product_id' => $product->id,
            'meta_title' => $validated['meta_title'] ?? $validated['name'],
            'meta_description' => $validated['meta_description'] ?? $validated['short_description'],
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'focus_keyword' => $validated['focus_keyword'] ?? null,
            'seo_content' => $validated['seo_content'] ?? null,
        ]);

        \Log::info('✅ SEO data saved', ['product_id' => $product->id]);

        // Step 6: Handle image uploads
        if ($request->hasFile('images')) {
            \Log::info('🟢 Uploading images', ['count' => count($request->file('images'))]);

            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'alt_text' => $validated['name'],
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);

                \Log::info('✅ Image uploaded', ['path' => $imagePath]);
            }
        }

        \Log::info('🎉 Product creation complete', ['product_id' => $product->id]);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully!');

    } catch (\Exception $e) {
        \Log::error('❌ Error creating product', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return redirect()->back()
            ->with('error', 'Error creating product: ' . $e->getMessage())
            ->withInput();
    }
}


    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $product->load(['category', 'variants', 'images', 'tags', 'seo']);
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the product
     */
    public function edit(Product $product)
    {
        $product->load(['variants', 'images', 'tags', 'seo']);
        $categories = Category::where('is_active', true)->get();
        $tags = Tag::where('is_active', true)->get();
        
        return view('admin.products.edit', compact('product', 'categories', 'tags'));
    }

    /**
     * Update the specified product
     */
    /**
 * Update the specified product
 */
public function update(Request $request, Product $product)
{
    // Validation
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'category_id' => 'required|exists:categories,id',
        'short_description' => 'nullable|string',
        'description' => 'required|string',
        'base_price' => 'required|numeric|min:0',
        'sale_price' => 'nullable|numeric|min:0',
        'sku' => 'required|string|unique:products,sku,' . $product->id,
        'stock_quantity' => 'required|integer|min:0',
        'brand' => 'nullable|string|max:255',
        'model' => 'nullable|string|max:255',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'tags' => 'array',
        'tags.*' => 'exists:tags,id',

        // Variants
        'variants' => 'array',
        'variants.*.id' => 'nullable|exists:product_variants,id',
        'variants.*.color_name' => 'required|string',
        'variants.*.color_code' => 'nullable|string',
        'variants.*.size' => 'nullable|string',
        'variants.*.material' => 'nullable|string',
        'variants.*.price' => 'required|numeric|min:0',
        'variants.*.sale_price' => 'nullable|numeric|min:0',
        'variants.*.stock_quantity' => 'required|integer|min:0',
        'variants.*.sku' => 'required|string',
        'variants.*.weight' => 'nullable|string',
        'variants.*.variant_description' => 'nullable|string',

        // SEO
        'meta_title' => 'nullable|string|max:255',
        'meta_description' => 'nullable|string',
        'meta_keywords' => 'nullable|string',
        'focus_keyword' => 'nullable|string',
        'seo_content' => 'nullable|string',

        // Images
        'images' => 'array',
        'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        'primary_image' => 'nullable|exists:product_images,id',
        'removed_images' => 'nullable|string',
    ]);

    try {
        \DB::beginTransaction();

        // Update product
        $product->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'category_id' => $validated['category_id'],
            'short_description' => $validated['short_description'] ?? null,
            'description' => $validated['description'],
            'base_price' => $validated['base_price'],
            'sale_price' => $validated['sale_price'] ?? null,
            'sku' => $validated['sku'],
            'stock_quantity' => $validated['stock_quantity'],
            'in_stock' => $validated['stock_quantity'] > 0,
            'brand' => $validated['brand'] ?? null,
            'model' => $validated['model'] ?? null,
            'is_featured' => $request->has('is_featured'),
            'is_active' => $request->has('is_active'),
        ]);

        // Sync tags
        $product->tags()->sync($validated['tags'] ?? []);

        // Update or create variants
        if (isset($validated['variants'])) {
            $existingVariantIds = [];
            
            foreach ($validated['variants'] as $variantData) {
                if (isset($variantData['id'])) {
                    // Update existing variant
                    $variant = ProductVariant::find($variantData['id']);
                    if ($variant) {
                        $variant->update($variantData);
                        $existingVariantIds[] = $variantData['id'];
                    }
                } else {
                    // Create new variant
                    $newVariant = ProductVariant::create(array_merge($variantData, ['product_id' => $product->id]));
                    $existingVariantIds[] = $newVariant->id;
                }
            }
            
            // Delete variants that were removed
            ProductVariant::where('product_id', $product->id)
                ->whereNotIn('id', $existingVariantIds)
                ->delete();
        }

        // Update SEO data
        if ($product->seo) {
            $product->seo->update([
                'meta_title' => $validated['meta_title'] ?? $product->name,
                'meta_description' => $validated['meta_description'] ?? $product->short_description,
                'meta_keywords' => $validated['meta_keywords'] ?? null,
                'focus_keyword' => $validated['focus_keyword'] ?? null,
                'seo_content' => $validated['seo_content'] ?? null,
            ]);
        } else {
            ProductSeo::create([
                'product_id' => $product->id,
                'meta_title' => $validated['meta_title'] ?? $product->name,
                'meta_description' => $validated['meta_description'] ?? $product->short_description,
                'meta_keywords' => $validated['meta_keywords'] ?? null,
                'focus_keyword' => $validated['focus_keyword'] ?? null,
                'seo_content' => $validated['seo_content'] ?? null,
            ]);
        }

        // Handle image removal
        if ($request->filled('removed_images')) {
            $removedImageIds = explode(',', $request->removed_images);
            $imagesToRemove = ProductImage::whereIn('id', $removedImageIds)->get();
            
            foreach ($imagesToRemove as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        // Handle primary image update
        if ($request->filled('primary_image')) {
            ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
            ProductImage::where('id', $request->primary_image)->update(['is_primary' => true]);
        }

        // Handle new image uploads
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $imagePath = $image->store('products', 'public');

                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imagePath,
                    'alt_text' => $validated['name'],
                    'is_primary' => $product->images()->count() === 0 && $index === 0,
                    'sort_order' => $product->images()->max('sort_order') + $index + 1,
                ]);
            }
        }

        \DB::commit();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully!');

    } catch (\Exception $e) {
        \DB::rollBack();
        
        \Log::error('Error updating product', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return redirect()->back()
            ->with('error', 'Error updating product: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        try {
            // Delete associated images from storage
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $product->delete();

            return redirect()->route('admin.products.index')
                ->with('success', 'Product deleted successfully!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting product: ' . $e->getMessage());
        }
    }

    /**
     * Calculate SEO Score (Basic implementation)
     */
    public function calculateSeoScore(Product $product)
    {
        $score = 0;
        $maxScore = 100;
        $feedback = [];

        // Check meta title
        if ($product->seo && $product->seo->meta_title) {
            $titleLength = strlen($product->seo->meta_title);
            if ($titleLength >= 50 && $titleLength <= 60) {
                $score += 20;
                $feedback[] = '✓ Meta title length is perfect';
            } else {
                $feedback[] = '⚠ Meta title should be 50-60 characters';
            }
        } else {
            $feedback[] = '✗ Meta title is missing';
        }

        // Check meta description
        if ($product->seo && $product->seo->meta_description) {
            $descLength = strlen($product->seo->meta_description);
            if ($descLength >= 120 && $descLength <= 160) {
                $score += 20;
                $feedback[] = '✓ Meta description length is perfect';
            } else {
                $feedback[] = '⚠ Meta description should be 120-160 characters';
            }
        } else {
            $feedback[] = '✗ Meta description is missing';
        }

        // Check focus keyword
        if ($product->seo && $product->seo->focus_keyword) {
            $score += 15;
            $feedback[] = '✓ Focus keyword is set';
        } else {
            $feedback[] = '✗ Focus keyword is missing';
        }

        // Check product name
        if (strlen($product->name) > 0) {
            $score += 10;
            $feedback[] = '✓ Product name is set';
        }

        // Check description
        if (strlen($product->description) > 200) {
            $score += 15;
            $feedback[] = '✓ Product description is detailed';
        } else {
            $feedback[] = '⚠ Product description could be more detailed';
        }

        // Check images
        if ($product->images->count() > 0) {
            $score += 10;
            $feedback[] = '✓ Product has images';
        } else {
            $feedback[] = '✗ Product has no images';
        }

        // Check alt text for images
        $hasAltText = $product->images->whereNotNull('alt_text')->count() > 0;
        if ($hasAltText) {
            $score += 10;
            $feedback[] = '✓ Images have alt text';
        } else {
            $feedback[] = '⚠ Add alt text to images for better SEO';
        }

        return [
            'score' => $score,
            'percentage' => round(($score / $maxScore) * 100),
            'feedback' => $feedback,
            'max_score' => $maxScore
        ];
    }

    /**
     * Show SEO Analysis for a product
     */
    public function seoAnalysis(Product $product)
    {
        $seoAnalysis = $this->calculateSeoScore($product);
        return view('admin.products.seo-analysis', compact('product', 'seoAnalysis'));
    }
}