<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;

// Public Product Routes
Route::controller(ProductController::class)->group(function () {
    Route::get('/products', 'index')->name('products.index');
    Route::get('/products/{slug}', 'show')->name('products.show');
    Route::get('/category/{slug}', 'category')->name('products.category');
    Route::get('/tag/{slug}', 'tag')->name('products.tag');
    Route::get('/search', 'search')->name('products.search');
    Route::get('/featured-products', 'featured')->name('products.featured');
    Route::get('/new-arrivals', 'newArrivals')->name('products.new-arrivals');
    Route::get('/sale', 'onSale')->name('products.on-sale');
});

Route::get('/', function () {
    $featuredProducts = \App\Models\Product::with(['primaryImage', 'variants'])
        ->featured()
        ->active()
        ->limit(8)
        ->get();
        
    $newArrivals = \App\Models\Product::with(['primaryImage', 'variants'])
        ->active()
        ->latest()
        ->limit(8)
        ->get();
        
    return view('welcome', compact('featuredProducts', 'newArrivals'));
})->name('home');

// Public Cart Routes (for adding items)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::get('/count', [CartController::class, 'getCount'])->name('count');
});

// Public Wishlist Routes (for checking status)
Route::prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/count', [WishlistController::class, 'getCount'])->name('count');
    Route::get('/check/{productId}', [WishlistController::class, 'check'])->name('check');
});


// Admin Routes (without auth for now)
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Dashboard
    Route::get('/dashboard', function () {
        $totalProducts = \App\Models\Product::count();
        $totalCategories = \App\Models\Category::count();
        $totalTags = \App\Models\Tag::count();
        $totalOrders = \App\Models\Order::count();
        $recentProducts = \App\Models\Product::latest()->limit(5)->get();
        
        return view('admin.dashboard', compact('totalProducts', 'totalCategories', 'totalTags', 'totalOrders', 'recentProducts'));
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
});

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes (require authentication)
Route::middleware('auth')->group(function () {


    // Checkout
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    });

    // Payment Routes
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::post('/razorpay/success', [PaymentController::class, 'razorpaySuccess'])->name('razorpay.success');
        Route::post('/razorpay/failure', [PaymentController::class, 'razorpayFailure'])->name('razorpay.failure');
        Route::post('/razorpay/webhook', [PaymentController::class, 'razorpayWebhook'])->name('razorpay.webhook');
    });

    // Order Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order}/track', [OrderController::class, 'track'])->name('track');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
    });

    // Protected Cart Routes
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::put('/update/{cart}', [CartController::class, 'update'])->name('update');
        Route::delete('/remove/{cart}', [CartController::class, 'remove'])->name('remove');
        Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    });

    // Protected Wishlist Routes
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/add', [WishlistController::class, 'add'])->name('add');
        Route::delete('/remove/{wishlist}', [WishlistController::class, 'remove'])->name('remove');
        Route::delete('/remove-product/{productId}', [WishlistController::class, 'removeByProduct'])->name('remove-by-product');
    });

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'dashboard'])->name('dashboard');
});