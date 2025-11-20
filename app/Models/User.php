<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Add this line

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar', 'date_of_birth', 'gender',
        'address', 'city', 'state', 'country', 'pincode', 'is_active', 'preferences'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
        'preferences' => 'array',
        'last_login_at' => 'datetime',
    ];

    // Carts relationship
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    // Wishlists relationship
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    // Orders relationship
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // Active cart items
    public function activeCartItems()
    {
        return $this->carts()->with(['product', 'variant']);
    }

    // Get cart total
    public function getCartTotalAttribute()
    {
        return $this->carts->sum(function($cart) {
            return $cart->price * $cart->quantity;
        });
    }

    // Get cart items count
    public function getCartItemsCountAttribute()
    {
        return $this->carts->sum('quantity');
    }

    // Check if product is in wishlist
    public function hasInWishlist($productId)
    {
        return $this->wishlists()->where('product_id', $productId)->exists();
    }

    // Check if product is in cart
    public function hasInCart($productId, $variantId = null)
    {
        return $this->carts()
            ->where('product_id', $productId)
            ->when($variantId, function($query) use ($variantId) {
                return $query->where('variant_id', $variantId);
            })
            ->exists();
    }
}