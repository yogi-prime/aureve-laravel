<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number', 'user_id', 'subtotal', 'tax_amount', 'shipping_charge',
        'discount_amount', 'total_amount', 'status', 'payment_status',
        'shipping_address', 'billing_address', 'customer_note', 'tracking_number',
        'shipped_at', 'delivered_at', 'cancelled_at',
        // Shiprocket fields
        'shiprocket_order_id', 'shiprocket_shipment_id', 'awb_code',
        'courier_name', 'courier_id', 'pickup_scheduled_at', 'shipping_weight',
        'shiprocket_response'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'pickup_scheduled_at' => 'datetime',
        'shiprocket_response' => 'array',
        'shipping_address' => 'array',
        'billing_address' => 'array',
    ];

    // User relationship
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Order items relationship
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Payment relationship
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // Scope for pending orders
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for completed orders
    public function scopeCompleted($query)
    {
        return $query->where('status', 'delivered');
    }

    // Check if order can be cancelled
    public function getCanBeCancelledAttribute()
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    // Check if order can be cancelled (method version for controller)
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    // Check if order is shipped via Shiprocket
    public function isShippedViaShiprocket(): bool
    {
        return !empty($this->shiprocket_order_id);
    }

    // Check if AWB is assigned
    public function hasAwb(): bool
    {
        return !empty($this->awb_code);
    }
}