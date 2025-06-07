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
        'property_id',
        'rating',
        'review'
    ];

    /**
     * Get the user that wrote the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the property that was reviewed.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
