<?php

use Illuminate\Support\Facades\Route;

// Redirect root to admin dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', function () {
        $totalProducts = \App\Models\Product::count();
        $totalCategories = \App\Models\Category::count();
        $totalTags = \App\Models\Tag::count();
        $totalOrders = \App\Models\Order::count();
        $totalBlogs = \App\Models\Blog::count();
        $recentProducts = \App\Models\Product::latest()->limit(5)->get();
        $recentBlogs = \App\Models\Blog::latest()->limit(5)->get();

        return view('admin.dashboard', compact('totalProducts', 'totalCategories', 'totalTags', 'totalOrders', 'totalBlogs', 'recentProducts', 'recentBlogs'));
    })->name('dashboard');

    // Product Management
    Route::resource('products', \App\Http\Controllers\Admin\ProductController::class);

    // SEO Analysis
    Route::get('/products/{product}/seo-analysis', [\App\Http\Controllers\Admin\ProductController::class, 'seoAnalysis'])
        ->name('products.seo-analysis');

    // Categories Management
    Route::resource('categories', \App\Http\Controllers\Admin\CategoryController::class);
    Route::post('/categories/{category}/toggle-status', [\App\Http\Controllers\Admin\CategoryController::class, 'toggleStatus'])
        ->name('categories.toggle-status');

    // Tags Management
    Route::resource('tags', \App\Http\Controllers\Admin\TagController::class);
    Route::post('/tags/{tag}/toggle-status', [\App\Http\Controllers\Admin\TagController::class, 'toggleStatus'])
        ->name('tags.toggle-status');

    // Blog Management
    Route::resource('blogs', \App\Http\Controllers\Admin\BlogController::class);
    Route::post('/blogs/{blog}/toggle-status', [\App\Http\Controllers\Admin\BlogController::class, 'toggleStatus'])
        ->name('blogs.toggle-status');
    Route::post('/blogs/{blog}/toggle-featured', [\App\Http\Controllers\Admin\BlogController::class, 'toggleFeatured'])
        ->name('blogs.toggle-featured');
});
