<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReview extends Model
{
    /** @use HasFactory<\Database\Factories\UserReviewFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'listing_id',
        'trip_id',
        'rating',
        'review',
        'status',
        'host_response_comment',
        'host_response_created_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'host_response_created_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that wrote the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the listing that was reviewed.
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the booking/trip associated with this review.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'trip_id');
    }

    /**
     * Check if review has host response.
     */
    public function hasHostResponse()
    {
        return !is_null($this->host_response_comment);
    }

    /**
     * Check if review is published.
     */
    public function isPublished()
    {
        return $this->status === 'published';
    }

    /**
     * Check if review is draft.
     */
    public function isDraft()
    {
        return $this->status === 'draft';
    }
}
