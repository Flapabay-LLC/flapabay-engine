<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoHost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'co_user_id',
        'listing_id',
        'status',
        'permissions',
        'joined_at',
        'last_active_at'
    ];

    protected $casts = [
        'permissions' => 'array',
        'joined_at' => 'datetime',
        'last_active_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function host()
    {
        return $this->user();
    }

    public function coHost()
    {
        return $this->belongsTo(User::class, 'co_user_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}