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
        'user_id',
        'title',
        'description',
        'location',
        'address',
        'county',
        'latitude',
        'longitude',
        'check_in_hour',
        'check_out_hour',
        'num_of_guests',
        'num_of_children',
        'maximum_guests',
        'allow_extra_guests',
        'neighborhood_area',
        'country',
        'show_contact_form_instead_of_booking',
        'allow_instant_booking',
        'currency',
        'price_range',
        'price',
        'price_per_night',
        'additional_guest_price',
        'children_price',
        'weekday_price',
        'weekend_price',
        'amenities',
        'house_rules',
        'page',
        'rating',
        'favorite',
        'images',
        'video_link',
        'verified',
        'listing_type',
        'featured_status',
        'listing_type_id',
        'listing_type',
        'has_unallocated_rooms',
        'num_of_bedrooms',
        'num_of_bathrooms',
        'num_of_quarters',
        'children_guests',
        'infant_guests',
        'adult_guests',
        'pet_guests',
        'about_place',
        'host_type',
        'occupation_type',
        'street',
        'city',
        'state',
        'zip_code',
        'square_feet',
        'place_items',
        'nights',
        'check_in_date',
        'check_out_date',
        'type_of_place',
        'coordinates',
        'every_bedroom_has_lock',
        'kind_of_bathrooms',
        'who_is_there',
        'status',
        'category_id',
        'published_at',
        'cancellation_policy',
        'is_completed',
        'availability_type',
        'flexible_period',
        'flexible_month',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'is_completed' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'check_in_hour' => 'datetime:H:i',
        'check_out_hour' => 'datetime:H:i',
        'allow_extra_guests' => 'boolean',
        'show_contact_form_instead_of_booking' => 'boolean',
        'allow_instant_booking' => 'boolean',
        'price_range' => 'array',
        'price' => 'decimal:2',
        'price_per_night' => 'decimal:2',
        'additional_guest_price' => 'decimal:2',
        'children_price' => 'decimal:2',
        'weekday_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
        'amenities' => 'array',
        'house_rules' => 'array',
        'rating' => 'decimal:2',
        'favorite' => 'boolean',
        'images' => 'array',
        'video_link' => 'array',
        'verified' => 'boolean',
        'has_unallocated_rooms' => 'boolean',
        'place_items' => 'array',
        'coordinates' => 'array',
        'every_bedroom_has_lock' => 'boolean',
        'is_instant_bookable' => 'boolean',
        'status' => 'string',
        'cancellation_policy' => 'boolean',
        'availability_type' => 'string',
        'flexible_period' => 'string',
        'flexible_month' => 'string',
        'title' => 'string',
    ];

    /**
     * Get the user (host) associated with the listing.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the host associated with the post (alias for user relationship).
     */
    public function host()
    {
        return $this->user();
    }

    // listing relationship removed - listings are now merged into listings

    /**
     * Get the category associated with the listing.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the listing type associated with the post.
     */
    public function listingType()
    {
        return $this->belongsTo(ListingType::class);
    }

    /**
     * Get the reviews associated with the post.
     */
    public function reviews()
    {
        return $this->hasMany(ListingReview::class, 'listing_id');
    }

    /**
     * Get the bookings associated with the post.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'listing_id');
    }

    /**
     * Get the favorites associated with the post.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'listing_id');
    }

    /**
     * Get the stay details associated with the listing.
     */
    public function stayDetails()
    {
        return $this->hasOne(Stay::class);
    }

    /**
     * Get the experience details associated with the listing.
     */
    public function experienceDetails()
    {
        return $this->hasOne(Experience::class);
    }

    /**
     * Legacy method for backward compatibility
     */
    public function stay()
    {
        return $this->stayDetails();
    }

    /**
     * Legacy method for backward compatibility
     */
    public function experience()
    {
        return $this->experienceDetails();
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
            return $this->stayDetails;
        } elseif ($this->listing_type === 'experience') {
            return $this->experienceDetails;
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
