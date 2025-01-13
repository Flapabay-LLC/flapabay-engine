<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    /** @use HasFactory<\Database\Factories\BookingFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property_id',
        'user_id',
        'start_date',
        'end_date',
        'guest_details',
        'guest_count',
        'booking_status',
        'payment_status',
        'payment_method',
        'payment_date',
        'cancellation_reason',
        'cancellation_date',
        'amount',
    ];

    /**
     * Get the property associated with the booking.
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    /**
     * Get the user who made the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
