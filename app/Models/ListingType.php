<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class listingType extends Model
{
    /** @use HasFactory<\Database\Factories\listingTypeFactory> */
    use HasFactory;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'black_icon',
        'white_icon',
        'description',
        'bg_color',
        'color',
        'type'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'black_icon' => 'string',
        'white_icon' => 'string',
        'description' => 'string',
        'bg_color' => 'string',
        'color' => 'string',
        'type' => 'string'
    ];

    /**
     * Relationship with listing model (if applicable).
     */
    public function listings()
    {
        return $this->hasMany(listing::class, 'listing_type_id');
    }
}
