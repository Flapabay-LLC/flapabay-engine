<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoHost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'host_id',
        'co_host_id',
        'property_id',
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
    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function coHost()
    {
        return $this->belongsTo(User::class, 'co_host_id');
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