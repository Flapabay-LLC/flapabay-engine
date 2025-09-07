<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    /** @use HasFactory<\Database\Factories\PropertyFactory> */
    use HasFactory;

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_PENDING = 'pending';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Get all available status values
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PUBLISHED,
            self::STATUS_PENDING,
            self::STATUS_ARCHIVED,
        ];
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'description',
        'location',
        'address',
        'country',
        'latitude',
        'longitude',
        'num_of_guests',
        'num_of_children',
        'has_unallocated_rooms',
        'num_of_bedrooms',
        'num_of_bathrooms',
        'num_of_quarters',
        'maximum_guests',
        'children_guests',
        'infant_guests',
        'adult_guests',
        'pet_guests',
        'allow_extra_guests',
        'neighborhood_area',
        'show_contact_form_instead_of_booking',
        'allow_instant_booking',
        'currency',
        'price_range',
        'price',
        'price_per_night',
        'additional_guest_price',
        'children_price',
        'weekday_price', // decimal
        'weekend_price', // decimal
        'page',
        'rating', //array [1,2,3,4,5]
        'images',
        'video_link',
        'verified',
        'featured_status', // enum: null, 'guest_favourite', 'featured'
        'property_type_id', //for filtering
        'category_id', //for filtering
        'tags', //for filtering
        'about_place',
        'host_type', //enum
        'occupation_type', //enum
        'street',
        'city',
        'favorites',
        'first_reserver',
        'state',
        'zip_code',
        'status',
        'square_feet',
        'place_items',
        'nights',
        'check_in_date',
        'check_out_date',
        'check_in_hour',
        'check_out_hour',
        'type_of_place', // string
        'address', // json
        'coordinates', // json
        'every_bedroom_has_lock', // boolean
        'kind_of_bathrooms', // string
        'who_is_there', // json
        'amenities', // json
        'house_rules',
        'favourites', // json
        'safety_items', // json
        'images', // json
        'features', // json
        'description', // text
        'host_booking_settings', // json
        'who_to_welcome_first_reservation', // string
        'discounts', // json
        'place_items', // json
        'status', // string - draft, published, pending, archived
        'user_id', // foreign key to users table
        'is_host', // boolean - indicates if user is a host
        'version', // integer for optimistic concurrency control
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'address' => 'array',
        'coordinates' => 'array',
        'who_is_there' => 'array',
        'amenities' => 'array',
        'favourites' => 'array',
        'safety_items' => 'array',
        'images' => 'array',
        'features' => 'array',
        'host_booking_settings' => 'array',
        'discounts' => 'array',
        'place_items' => 'array',
        'every_bedroom_has_lock' => 'boolean',
        'weekday_price' => 'decimal:2',
        'weekend_price' => 'decimal:2',
        'status' => 'string',
        'is_host' => 'boolean',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    public static function createProperty($data)
    {
        return Property::create([
            'title' => $data['title'], // Property title
            'description' => $data['description'], // Property description
            'location' => $data['location'], // Location of the property
            'address' => $data['address'], // Address of the property
            'latitude' => $data['latitude'], // Latitude
            'longitude' => $data['longitude'], // Longitude
            'check_in_hour' => $data['check_in_hour'], // Check-in hour
            'check_out_hour' => $data['check_out_hour'], // Check-out hour
            'num_of_guests' => $data['num_of_guests'], // Number of guests
            'num_of_children' => $data['num_of_children'], // Number of children
            'maximum_guests' => $data['maximum_guests'], // Maximum guests allowed
            'country' => $data['country'], // Country
            'currency' => $data['currency'], // Currency
            'price_range' => $data['price_range'], // Price range
            'price' => $data['price'], // Price
            'additional_guest_price' => $data['additional_guest_price'], // Additional guest price
            'children_price' => $data['children_price'], // Children's price
            'amenities' => json_encode($data['amenities']), // Convert array to JSON string
            'house_rules' => json_encode($data['house_rules']), // Convert array to JSON string
            'video_link' => $data['video_link'], // Video link (if any)
            'verified' => $data['verified'], // Verification status (1/0)
            'num_of_bedrooms' => $data['num_of_bedrooms'], // Number of bedrooms
            'num_of_bathrooms' => $data['num_of_bathrooms'], // Number of bathrooms
            'num_of_quarters' => $data['num_of_quarters'], // Number of quarters
            'user_id' => $data['user_id'], // User ID (property owner)
            'is_host' => $data['is_host'] ?? true, // Boolean to indicate if user is host
        ]);
    }


    /**
     * Scope a query to only include verified properties.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope a query to only include favorite properties.
     */
    public function scopeFavorite($query)
    {
        return $query->where('favorite', true);
    }

    /**
     * Scope a query to filter by property type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('property_type', $type);
    }

    /**
     * Scope a query to filter properties within a price range.
     */
    public function scopeWithinPriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Relationship with Post model (if needed).
     */
    public function listing()
    {
        return $this->hasOne(\App\Models\Listing::class, 'property_id');
    }

    /**
     * Relationship with User model (property owner).
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get the formatted price range.
     */
    public function getFormattedPriceRangeAttribute()
    {
        if ($this->price_range) {
            return 'Min: ' . $this->price_range['min'] . ' - Max: ' . $this->price_range['max'];
        }
        return null;
    }

    /**
     * Get the full address as a formatted string.
     */
    public function getFullAddressAttribute()
    {
        return $this->address . ', ' . $this->county . ', ' . $this->country;
    }

    /**
     * Determine if the property allows instant booking.
     */
    public function allowsInstantBooking()
    {
        return $this->allow_instant_booking;
    }

    /**
     * Check if a property is marked as a favorite.
     */
    public function isFavorite()
    {
        return $this->favorite;
    }

    /**
     * Get the URL for the first image (if images are an array).
     */
    public function getFirstImageUrlAttribute()
    {
        return $this->images && count($this->images) > 0 ? $this->images[0] : null;
    }

    /**
     * Get the Google Maps URL for the property location.
     */
    public function getGoogleMapsUrlAttribute()
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        return null;
    }

    /**
     * Get the availability information for the property (matches getPropertyAvailabilityDates controller logic)
     */
    public function getAvailabilityAttribute()
    {
        // Retrieve the property fields (already loaded on this model)
        $availability = [
            'property_id' => $this->id,
            'check_in_date' => $this->check_in_date,
            'check_out_date' => $this->check_out_date,
            'check_in_hour' => $this->check_in_hour,
            'check_out_hour' => $this->check_out_hour,
            'allow_instant_booking' => $this->allow_instant_booking,
            'cancellation_policy' => null,
            'availability_type' => null,
            'flexible_period' => null,
            'flexible_month' => null,
            'is_available' => !is_null($this->check_in_date) && !is_null($this->check_out_date),
        ];

        // Try to get related listing fields if loaded or available
        $listing = $this->relationLoaded('listing') ? $this->listing : $this->listing()->first();
        if ($listing) {
            $availability['cancellation_policy'] = $listing->cancellation_policy;
            $availability['availability_type'] = $listing->availability_type;
            $availability['flexible_period'] = $listing->flexible_period;
            $availability['flexible_month'] = $listing->flexible_month;
        }
        return $availability;
    }

    /**
     * Get the category that owns the property.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the property type that owns the property.
     */
    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }

    /**
     * Get the reviews for the property.
     */
    public function reviews()
    {
        return $this->hasMany(UserReview::class);
    }

    public function images()
    {
        return $this->hasMany(PropertyImage::class);
    }



    public function setDraft(): void
    {
        $this->status = self::STATUS_DRAFT;
    }

    public function setPublished(): void
    {
        $this->status = self::STATUS_PUBLISHED;
    }

    public function setPending(): void
    {
        $this->status = self::STATUS_PENDING;
    }

    public function setArchived(): void
    {
        $this->status = self::STATUS_ARCHIVED;
    }

    // Query scopes
    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeArchived($query)
    {
        return $query->where('status', self::STATUS_ARCHIVED);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if property is in draft status
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if property is published
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Check if property is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if property is archived
     */
    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'property_amenities');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function coHosts()
    {
        return $this->hasMany(CoHost::class);
    }


}
