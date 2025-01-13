<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    /** @use HasFactory<\Database\Factories\AvailabilityFactory> */
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'property_id',
        'date_range',
        // 'availability',
        'price_dates',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'date_range' => 'array', // Cast date_range as an array (JSON)
        // 'availability' => 'array', // Cast availability as an array (JSON)
        'price_dates' => 'array', // Cast price_dates as an array (JSON)
    ];

    /**
     * Relationship with Property model.
     */
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the available dates as a formatted string.
     */
    public function getFormattedAvailabilityAttribute()
    {
        return implode(', ', $this->availability);
    }

    /**
     * Check if a specific date is available for the property.
     *
     * @param string $date
     * @return bool
     */
    public function isDateAvailable($date)
    {
        return in_array($date, $this->availability);
    }

    /**
     * Get the overridden price for a specific date, if any.
     *
     * @param string $date
     * @return float|null
     */
    public function getPriceForDate($date)
    {
        foreach ($this->price_dates as $priceDate) {
            if ($priceDate['date'] == $date) {
                return $priceDate['price'];
            }
        }

        return null; // Return null if no price override exists
    }
}
