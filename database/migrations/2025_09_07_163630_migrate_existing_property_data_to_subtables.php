<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Property;
use App\Models\Listing;
use App\Models\Stay;
use App\Models\Experience;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing property data to subtables
        $this->migrateExistingData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear migrated data from subtables
        DB::table('stays')->truncate();
        DB::table('experiences')->truncate();
        
        // Note: We don't restore data back to properties table
        // as this would require backing up the original data
    }

    /**
     * Migrate existing property data to appropriate subtables
     */
    private function migrateExistingData(): void
    {
        // Get all properties with their listings
        $properties = Property::with('listing')->get();

        foreach ($properties as $property) {
            // Create or update listing if it doesn't exist
            $listing = $property->listing;
            if (!$listing) {
                $listing = Listing::create([
                    'property_id' => $property->id,
                    'host_id' => $property->host_id ?? 1, // Default host if not set
                    'listing_type' => $this->determineListingType($property),
                    'status' => $property->status ?? false,
                    'is_completed' => $property->is_completed ?? false
                ]);
            }

            // Migrate data based on listing type
            if ($listing->listing_type === 'stay') {
                $this->migrateToStay($property, $listing);
            } elseif ($listing->listing_type === 'experience') {
                $this->migrateToExperience($property, $listing);
            }
        }
    }

    /**
     * Determine listing type based on property data
     */
    private function determineListingType(Property $property): string
    {
        // Logic to determine if property is a stay or experience
        // You can customize this based on your business logic
        
        // If property has bedrooms/bathrooms, it's likely a stay
        if ($property->bedrooms > 0 || $property->bathrooms > 0) {
            return 'stay';
        }
        
        // If property has duration field or activity-related fields, it might be an experience
        // For now, default to 'stay' for existing properties
        return 'stay';
    }

    /**
     * Migrate property data to stays table
     */
    private function migrateToStay(Property $property, Listing $listing): void
    {
        // Check if stay record already exists
        $existingStay = Stay::where('listing_id', $listing->id)->first();
        if ($existingStay) {
            return; // Skip if already migrated
        }

        Stay::create([
            'listing_id' => $listing->id,
            'check_in_time' => '15:00', // Default check-in time
            'check_out_time' => '11:00', // Default check-out time
            'minimum_stay' => 1, // Default minimum stay
            'maximum_stay' => null,
            'cleaning_fee' => 0, // Default cleaning fee
            'security_deposit' => 0, // Default security deposit
            'extra_guest_fee' => 0, // Default extra guest fee
            'weekend_pricing' => null,
            'monthly_discount' => null,
            'weekly_discount' => null,
            'cancellation_policy' => 'moderate', // Default policy
            'house_rules' => $property->house_rules ? json_encode($property->house_rules) : null,
            'instant_book' => false, // Default to false
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Migrate property data to experiences table
     */
    private function migrateToExperience(Property $property, Listing $listing): void
    {
        // Check if experience record already exists
        $existingExperience = Experience::where('listing_id', $listing->id)->first();
        if ($existingExperience) {
            return; // Skip if already migrated
        }

        Experience::create([
            'listing_id' => $listing->id,
            'duration_hours' => 2, // Default 2 hours
            'duration_minutes' => 0,
            'minimum_group_size' => 1, // Default minimum group size
            'maximum_group_size' => 10, // Default maximum group size
            'activity_type' => 'outdoor', // Default activity type
            'what_to_bring' => null,
            'what_is_included' => null,
            'meeting_point' => $property->address ?? null,
            'languages_offered' => json_encode(['English']), // Default language
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
};
