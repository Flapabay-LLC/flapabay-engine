<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'listing_id',
        'check_in_date',
        'check_out_date',
        'number_of_guests',
        'number_of_children',
        'number_of_infants',
        'number_of_pets',
        'total_price',
        'currency',
        'status',
        'special_requests',
        'cancellation_reason',
        'cancelled_at',
        'is_instant_booking',
        'guest_phone',
        'guest_email',
        'price_breakdown', // JSON field for price breakdown
        'expires_at', // new field
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'cancelled_at' => 'datetime',
        'is_instant_booking' => 'boolean',
        'total_price' => 'decimal:2',
        'price_breakdown' => 'array', // Cast as array
        'expires_at' => 'datetime', // new cast
    ];

    /**
     * Automatically set expires_at to 5 days from now on creation if not set.
     */
    protected static function booted()
    {
        static::creating(function ($reservation) {
            if (empty($reservation->expires_at)) {
                $reservation->expires_at = now()->addDays(5);
            }
        });
    }

    /**
     * price_breakdown: stores the detailed price calculation as JSON/array
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the property that was reserved.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Scope a query to only include pending reservations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include confirmed reservations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope a query to only include cancelled reservations.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include completed reservations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
} 