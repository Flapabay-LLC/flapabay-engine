<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class listingImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'image_url',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function listing()
    {
        return $this->belongsTo(listing::class);
    }
} 