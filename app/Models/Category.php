<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;
/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'icon',
        'icon_alt',
        'description',
    ];


    /**
     * Get the category name in uppercase (Optional accessor).
     */
    public function getUppercaseNameAttribute()
    {
        return strtoupper($this->name);
    }

    /**
     * Relationship with listing model (if applicable).
     */
    public function listings()
    {
        return $this->belongsToMany(listing::class, 'category_listing', 'category_id', 'listing_id');
    }
}
