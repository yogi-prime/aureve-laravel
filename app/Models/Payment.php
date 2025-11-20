<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'payment_id', 'payment_method', 'amount', 'currency', 'status',
        'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature',
        'payment_response', 'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_response' => 'array',
        'paid_at' => 'datetime',
    ];

    // Order relationship
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Scope for successful payments
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    // Check if payment is successful
    public function getIsSuccessfulAttribute()
    {
        return $this->status === 'completed';
    }
}