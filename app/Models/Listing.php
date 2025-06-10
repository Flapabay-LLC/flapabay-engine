<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    /** @use HasFactory<\Database\Factories\ListingFactory> */
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'host_id',
        'title',
        'description',
        'property_id',
        'property_type_id',
        'price_per_night',
        'bedrooms',
        'bathrooms',
        'max_guests',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'latitude',
        'longitude',
        'status',
        'is_instant_bookable',
        'cancellation_policy',
        'house_rules',
        'check_in_time',
        'check_out_time',
        'category_id',
        'published_at',
        'is_completed'
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
        'status' => 'boolean',
        'cancellation_policy' => 'boolean',
        'is_completed' => 'boolean',
        'published_at' => 'datetime'
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
     * Get the amenities associated with the post.
     */
    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'listing_amenities');
    }

    /**
     * Get the images associated with the post.
     */
    public function images()
    {
        return $this->hasMany(ListingImage::class);
    }

    /**
     * Get the place items associated with the post.
     */
    public function placeItems()
    {
        return $this->belongsToMany(PlaceItem::class, 'listing_place_items');
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
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include posts for a specific category.
     */
    public function scopeOfCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
