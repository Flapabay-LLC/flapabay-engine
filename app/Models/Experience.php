<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'listing_id',
        'duration_hours',
        'duration_minutes',
        'minimum_group_size',
        'maximum_group_size',
        'activity_type',
        'difficulty_level',
        'physical_activity_level',
        'minimum_age',
        'maximum_age',
        'children_allowed',
        'age_restrictions_note',
        'available_times',
        'available_days',
        'flexible_scheduling',
        'advance_booking_hours',
        'cancellation_hours',
        'what_to_bring',
        'what_is_included',
        'what_is_not_included',
        'meeting_point',
        'safety_requirements',
        'languages_offered',
        'price_per_person',
        'group_discount_percentage',
        'group_discount_threshold',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'children_allowed' => 'boolean',
        'available_times' => 'array',
        'available_days' => 'array',
        'flexible_scheduling' => 'boolean',
        'languages_offered' => 'array',
        'price_per_person' => 'decimal:2',
        'group_discount_percentage' => 'decimal:2',
    ];

    /**
     * Get the listing that owns the experience.
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the total duration in minutes.
     */
    public function getTotalDurationMinutesAttribute()
    {
        return ($this->duration_hours * 60) + $this->duration_minutes;
    }

    /**
     * Get formatted duration string.
     */
    public function getFormattedDurationAttribute()
    {
        $hours = $this->duration_hours;
        $minutes = $this->duration_minutes;
        
        if ($hours && $minutes) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours) {
            return "{$hours}h";
        } elseif ($minutes) {
            return "{$minutes}m";
        }
        
        return 'Duration not specified';
    }
}
