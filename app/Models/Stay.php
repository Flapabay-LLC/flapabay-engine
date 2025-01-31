<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stay extends Model
{
    /** @use HasFactory<\Database\Factories\StayFactory> */
    use HasFactory;
    protected $fillable = [
        'user_id',
        'host_id',
        'property_id',
        'title',
        'description',
        'about_this_place',
        'max_guests',
        'price_per_night',
        'amenities',
        'images',
        'videos',
        'total_nights',
        'total_price',
        'starting',
        'ending',
        'is_available'
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'videos' => 'array',
        'is_available' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }
}
