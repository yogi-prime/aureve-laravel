<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display all products with pagination
     */
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'primaryImage', 'variants', 'tags'])
            ->active()
            ->withCount('orderItems')
            ->latest();

        // Search functionality
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('brand', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        // Category filter
        if ($request->has('category') && $request->category != '') {
            $query->where('category_id', $request->category);
        }

        // Price range filter
        if ($request->has('min_price') && $request->min_price != '') {
            $query->where('base_price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price != '') {
            $query->where('base_price', '<=', $request->max_price);
        }

        // Sort options
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('base_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('base_price', 'desc');
                break;
            case 'popular':
                $query->orderBy('order_items_count', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            default:
                $query->latest();
        }

        $products = $query->paginate(12);
        $categories = Category::where('is_active', true)->get();
        
        // Get min and max prices for filter
        $minPrice = Product::active()->min('base_price');
        $maxPrice = Product::active()->max('base_price');

        return view('products.index', compact('products', 'categories', 'minPrice', 'maxPrice'));
    }

    /**
     * Display single product details
     */
    public function show($slug): View
    {
        $product = Product::with([
            'category', 
            'images', 
            'variants' => function($query) {
                $query->where('is_active', true);
            },
            'tags',
            'seo'
        ])
        ->where('slug', $slug)
        ->active()
        ->firstOrFail();

        // Increment view count
        $product->increment('view_count');

        // Related products (same category)
        $relatedProducts = Product::with(['primaryImage', 'variants'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->limit(8)
            ->get();

        return view('products.show', compact('product', 'relatedProducts'));
    }

    /**
     * Display products by category
     */
    public function category($slug): View
    {
        $category = Category::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $products = Product::with(['category', 'primaryImage', 'variants'])
            ->where('category_id', $category->id)
            ->active()
            ->paginate(12);

        $categories = Category::where('is_active', true)->get();

        return view('products.category', compact('category', 'products', 'categories'));
    }

    /**
     * Display products by tag
     */
    public function tag($slug): View
    {
        $tag = Tag::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $products = $tag->products()
            ->with(['category', 'primaryImage', 'variants'])
            ->active()
            ->paginate(12);

        return view('products.tag', compact('tag', 'products'));
    }

    /**
     * Search products
     */
    public function search(Request $request): View
    {
        $search = $request->get('q', '');

        $products = Product::with(['category', 'primaryImage', 'variants'])
            ->where(function($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhere('brand', 'LIKE', "%{$search}%")
                      ->orWhere('short_description', 'LIKE', "%{$search}%");
            })
            ->active()
            ->paginate(12);

        return view('products.search', compact('products', 'search'));
    }

    /**
     * Get featured products
     */
    public function featured(): View
    {
        $products = Product::with(['category', 'primaryImage', 'variants'])
            ->featured()
            ->active()
            ->latest()
            ->paginate(12);

        return view('products.featured', compact('products'));
    }

    /**
     * Get new arrivals
     */
    public function newArrivals(): View
    {
        $products = Product::with(['category', 'primaryImage', 'variants'])
            ->active()
            ->latest()
            ->limit(20)
            ->get();

        return view('products.new-arrivals', compact('products'));
    }

    /**
     * Get products on sale
     */
    public function onSale(): View
    {
        $products = Product::with(['category', 'primaryImage', 'variants'])
            ->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'base_price')
            ->active()
            ->latest()
            ->paginate(12);

        return view('products.on-sale', compact('products'));
    }
}