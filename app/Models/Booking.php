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
        'listing_id',
        'booking_type',
        'user_id',
        'start_date',
        'end_date',
        'guest_details',
        'guest_count',
        'booking_status', // possible: pending, rejected, confirmed, check_in, check_out, completed
        'payment_status',
        'payment_method',
        'payment_date',
        'cancellation_reason',
        'cancellation_date',
        'amount',
        'reservation_id', // new field
    ];

    /**
     * Booking status values:
     * - pending
     * - rejected
     * - confirmed
     * - check_in
     * - check_out
     * - completed
     */

    /**
     * Get the listing associated with the booking.
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }

    /**
     * Get the user who made the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the reservation associated with the booking.
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    /**
     * Get the user reviews for this booking.
     */
    public function userReviews()
    {
        return $this->hasMany(UserReview::class, 'trip_id');
    }

    /**
     * Get the payment associated with the booking.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class, 'booking_id');
    }
}
