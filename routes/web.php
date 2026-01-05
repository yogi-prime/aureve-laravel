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
        $totalCollections = \App\Models\Collection::count();
        $totalTags = \App\Models\Tag::count();
        $totalOrders = \App\Models\Order::count();
        $totalBlogs = \App\Models\Blog::count();
        $recentProducts = \App\Models\Product::latest()->limit(5)->get();
        $recentBlogs = \App\Models\Blog::latest()->limit(5)->get();

        return view('admin.dashboard', compact('totalProducts', 'totalCategories', 'totalCollections', 'totalTags', 'totalOrders', 'totalBlogs', 'recentProducts', 'recentBlogs'));
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

    // Collections Management
    Route::resource('collections', \App\Http\Controllers\Admin\CollectionController::class);
    Route::post('/collections/{collection}/toggle-status', [\App\Http\Controllers\Admin\CollectionController::class, 'toggleStatus'])
        ->name('collections.toggle-status');
    Route::get('/collections/{collection}/manage-products', [\App\Http\Controllers\Admin\CollectionController::class, 'manageProducts'])
        ->name('collections.manage-products');
    Route::post('/collections/{collection}/update-products', [\App\Http\Controllers\Admin\CollectionController::class, 'updateProducts'])
        ->name('collections.update-products');

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

    // Blog Categories Management
    Route::resource('blog-categories', \App\Http\Controllers\Admin\BlogCategoryController::class);
    Route::post('/blog-categories/{blogCategory}/toggle-status', [\App\Http\Controllers\Admin\BlogCategoryController::class, 'toggleStatus'])
        ->name('blog-categories.toggle-status');

    // Blog Tags Management
    Route::resource('blog-tags', \App\Http\Controllers\Admin\BlogTagController::class);
    Route::post('/blog-tags/{blogTag}/toggle-status', [\App\Http\Controllers\Admin\BlogTagController::class, 'toggleStatus'])
        ->name('blog-tags.toggle-status');

    // Size Guides Management
    Route::resource('size-guides', \App\Http\Controllers\Admin\SizeGuideController::class);
    Route::post('/size-guides/{sizeGuide}/toggle-status', [\App\Http\Controllers\Admin\SizeGuideController::class, 'toggleStatus'])
        ->name('size-guides.toggle-status');

    // Orders Management
    Route::get("/orders", [\App\Http\Controllers\Admin\OrderController::class, "index"])->name("orders.index");
    Route::get("/orders/{order}", [\App\Http\Controllers\Admin\OrderController::class, "show"])->name("orders.show");
    Route::patch("/orders/{order}/status", [\App\Http\Controllers\Admin\OrderController::class, "updateStatus"])->name("orders.update-status");
    Route::patch("/orders/{order}/payment-status", [\App\Http\Controllers\Admin\OrderController::class, "updatePaymentStatus"])->name("orders.update-payment-status");

    // Shiprocket / Shipping Management
    Route::prefix('orders/{order}/shiprocket')->name('shiprocket.')->group(function () {
        Route::post('/create', [\App\Http\Controllers\Admin\ShiprocketController::class, 'createOrder'])->name('create');
        Route::get('/couriers', [\App\Http\Controllers\Admin\ShiprocketController::class, 'getAvailableCouriers'])->name('couriers');
        Route::post('/awb', [\App\Http\Controllers\Admin\ShiprocketController::class, 'assignAWB'])->name('awb');
        Route::post('/pickup', [\App\Http\Controllers\Admin\ShiprocketController::class, 'schedulePickup'])->name('pickup');
        Route::post('/ship', [\App\Http\Controllers\Admin\ShiprocketController::class, 'shipOrder'])->name('ship');
        Route::get('/track', [\App\Http\Controllers\Admin\ShiprocketController::class, 'track'])->name('track');
        Route::post('/cancel', [\App\Http\Controllers\Admin\ShiprocketController::class, 'cancelShipment'])->name('cancel');
        Route::get('/label', [\App\Http\Controllers\Admin\ShiprocketController::class, 'generateLabel'])->name('label');
        Route::get('/invoice', [\App\Http\Controllers\Admin\ShiprocketController::class, 'generateInvoice'])->name('invoice');
    });
});
