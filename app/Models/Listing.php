<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    /** @use HasFactory<\Database\Factories\ListingFactory> */
    use HasFactory;

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_PENDING = 'pending';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Get all available status values
     */
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PUBLISHED,
            self::STATUS_PENDING,
            self::STATUS_ARCHIVED,
            self::STATUS_INACTIVE,
        ];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'host_id',
        'description',
        'property_id',
        'status',
        'listing_type',
        'is_instant_bookable',
        'cancellation_policy',
        'category_id',
        'published_at',
        'is_completed',
        'availability_type',
        'flexible_period',
        'flexible_month',
        'title',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'price_per_night' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_instant_bookable' => 'boolean',
        'status' => 'string',
        'cancellation_policy' => 'boolean',
        'is_completed' => 'boolean',
        'published_at' => 'datetime',
        'availability_type' => 'string',
        'flexible_period' => 'string',
        'flexible_month' => 'string',
        'title' => 'string',
    ];

    /**
     * Get the host associated with the post.
     */
    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Get the property associated with the listing.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the property type associated with the post.
     */
    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }

    /**
     * Get the reviews associated with the post.
     */
    public function reviews()
    {
        return $this->hasMany(PropertyReview::class, 'property_id');
    }

    /**
     * Get the bookings associated with the post.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'property_id');
    }

    /**
     * Get the favorites associated with the post.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'property_id');
    }

    /**
     * Get the stay details if this is a stay listing.
     */
    public function stay()
    {
        return $this->hasOne(Stay::class);
    }

    /**
     * Get the experience details if this is an experience listing.
     */
    public function experience()
    {
        return $this->hasOne(Experience::class);
    }

    /**
     * Get the amenities associated with the listing.
     */
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'listing_amenities');
    }

    /**
     * Get the place items associated with the listing.
     */
    public function placeItems()
    {
        return $this->belongsToMany(PlaceItem::class, 'listing_place_items');
    }

    /**
     * Get the images associated with the listing.
     */
    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }

    /**
     * Get the type-specific details based on listing_type.
     */
    public function getTypeSpecificDetailsAttribute()
    {
        if ($this->listing_type === 'stay') {
            return $this->stay;
        } elseif ($this->listing_type === 'experience') {
            return $this->experience;
        }
        return null;
    }

    /**
     * Check if this listing is a stay.
     */
    public function isStay()
    {
        return $this->listing_type === 'stay';
    }

    /**
     * Check if this listing is an experience.
     */
    public function isExperience()
    {
        return $this->listing_type === 'experience';
    }

    /**
     * Check if listing is draft
     */
    public function isDraft()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if listing is published
     */
    public function isPublished()
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if listing is pending
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if listing is archived
     */
    public function isArchived()
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * Set status to draft
     */
    public function setDraft()
    {
        $this->status = self::STATUS_DRAFT;
        return $this;
    }

    /**
     * Set status to published
     */
    public function setPublished()
    {
        $this->status = self::STATUS_PUBLISHED;
        $this->published_at = now();
        return $this;
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include posts for a specific category.
     */
    public function scopeOfCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
