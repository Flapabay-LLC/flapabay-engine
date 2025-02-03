<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    /** @use HasFactory<\Database\Factories\AmenityFactory> */
    use HasFactory;

    public $fillable = [
        'name',
        'description',
        'white_icon',
        'black_icon',
        'svg',
        'uri'
    ];
}
