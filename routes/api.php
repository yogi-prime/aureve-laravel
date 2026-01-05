<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\SizeGuideController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Blog APIs (Public) - Insights
Route::prefix('blogs')->group(function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::get('/featured', [BlogController::class, 'featured']);
    Route::get('/categories', [BlogController::class, 'categories']);
    Route::get('/tags', [BlogController::class, 'tags']);
    Route::get('/archive', [BlogController::class, 'archive']);
    Route::get('/sitemap', [BlogController::class, 'sitemap']);
    Route::get('/{slug}', [BlogController::class, 'show']);
    Route::get('/{slug}/related', [BlogController::class, 'related']);
});

// Public APIs
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/products/slug/{slug}', [ProductController::class, 'showBySlug']);
Route::get('/products/{productId}/similar', [ProductController::class, 'similarProducts']);

// Category APIs
Route::get('/categories/slug/{slug}', [CategoryController::class, 'showBySlug']);
Route::get('/categories/slug/{slug}/products', [CategoryController::class, 'productsBySlug']);
Route::get('/categories/with-counts', [CategoryController::class, 'indexWithCounts']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Auth APIs
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/google', [AuthController::class, 'googleLogin']);

// Protected APIs (Authentication Required)
Route::middleware('auth:sanctum')->group(function () {
    // User APIs
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Cart APIs
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update/{cart}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{cart}', [CartController::class, 'remove']);
    Route::post('/cart/clear', [CartController::class, 'clear']);

    // Wishlist APIs
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/add', [WishlistController::class, 'add']);
    Route::post('/wishlist/remove', [WishlistController::class, 'remove']);
    Route::get('/wishlist/check/{productId}', [WishlistController::class, 'check']);

    // Checkout Routes
    Route::prefix('checkout')->group(function () {
        Route::get('/', [CheckoutController::class, 'index']);
        Route::post('/process', [CheckoutController::class, 'process']);
    });

    // Payment Routes
    Route::prefix('payment')->group(function () {
        Route::post('/razorpay/success', [PaymentController::class, 'razorpaySuccess']);
        Route::post('/razorpay/failure', [PaymentController::class, 'razorpayFailure']);
    });

    // Order Routes
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{order}', [OrderController::class, 'show']);
        Route::get('/{order}/track', [OrderController::class, 'track']);
        Route::post('/{order}/cancel', [OrderController::class, 'cancel']);
    });
});

// Size Guide APIs (Public)
Route::prefix('size-guides')->group(function () {
    Route::get('/', [SizeGuideController::class, 'index']);
    Route::get('/dropdown', [SizeGuideController::class, 'dropdown']);
    Route::get('/{id}', [SizeGuideController::class, 'show']);
    Route::post('/', [SizeGuideController::class, 'store']);
    Route::post('/{id}', [SizeGuideController::class, 'update']); // POST for form-data with file
    Route::delete('/{id}', [SizeGuideController::class, 'destroy']);
});

// CORS handle karne ke liye
Route::options('/{any}', function () {
    return response()->json();
})->where('any', '.*');
