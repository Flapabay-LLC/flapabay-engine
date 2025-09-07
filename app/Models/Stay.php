<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stay extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'listing_id',
        'cleaning_fee',
        'minimum_nights',
        'maximum_nights',
        'security_deposit',
        'weekend_price_multiplier',
        'monthly_discount_percentage',
        'weekly_discount_percentage',
        'earliest_check_in',
        'latest_check_in',
        'check_out_time',
        'self_check_in',
        'check_in_instructions',
        'advance_booking_days',
        'preparation_time_hours',
        'instant_book_eligible',
        'pet_fee',
        'extra_guest_fee',
        'extra_guest_threshold',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'cleaning_fee' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'weekend_price_multiplier' => 'decimal:2',
        'monthly_discount_percentage' => 'decimal:2',
        'weekly_discount_percentage' => 'decimal:2',
        'earliest_check_in' => 'datetime:H:i',
        'latest_check_in' => 'datetime:H:i',
        'check_out_time' => 'datetime:H:i',
        'self_check_in' => 'boolean',
        'instant_book_eligible' => 'boolean',
        'pet_fee' => 'decimal:2',
        'extra_guest_fee' => 'decimal:2',
    ];

    /**
     * Get the listing that owns the stay.
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }
}
