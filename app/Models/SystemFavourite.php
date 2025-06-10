<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemFavourite extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];
} 