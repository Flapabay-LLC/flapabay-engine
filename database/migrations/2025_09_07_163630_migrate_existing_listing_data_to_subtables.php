<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
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
        // Migration disabled: listings table has been dropped and merged into listings
        // Data migration is no longer applicable as the source table doesn't exist
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear migrated data from subtables
        DB::table('stays')->truncate();
        DB::table('experiences')->truncate();
        
        // Note: We don't restore data back to listings table
        // as this would require backing up the original data
    }

    /**
     * Migrate existing listing data to appropriate subtables
     */
    private function migrateExistingData(): void
    {
        // Get all listings with their listings
        $listings = listing::with('listing')->get();

        foreach ($listings as $listing) {
            // Create or update listing if it doesn't exist
            $listing = $listing->listing;
            if (!$listing) {
                $listing = Listing::create([
                    'listing_id' => $listing->id,
                    'user_id' => $listing->user_id ?? 1, // Default host if not set
                    'listing_type' => $this->determineListingType($listing),
                    'status' => $listing->status ?? false,
                    'is_completed' => $listing->is_completed ?? false
                ]);
            }

            // Migrate data based on listing type
            if ($listing->listing_type === 'stay') {
                $this->migrateToStay($listing, $listing);
            } elseif ($listing->listing_type === 'experience') {
                $this->migrateToExperience($listing, $listing);
            }
        }
    }

    /**
     * Determine listing type based on listing data
     */
    private function determineListingType(Listing $listing): string
    {
        // Logic to determine if listing is a stay or experience
        // You can customize this based on your business logic
        
        // If listing has bedrooms/bathrooms, it's likely a stay
        if ($listing->bedrooms > 0 || $listing->bathrooms > 0) {
            return 'stay';
        }
        
        // If listing has duration field or activity-related fields, it might be an experience
        // For now, default to 'stay' for existing listings
        return 'stay';
    }

    /**
     * Migrate listing data to stays table
     */
    private function migrateToStay(Listing $listing): void
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
            'house_rules' => $listing->house_rules ? json_encode($listing->house_rules) : null,
            'instant_book' => false, // Default to false
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Migrate listing data to experiences table
     */
    private function migrateToExperience(Listing $listing): void
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
            'meeting_point' => $listing->address ?? null,
            'languages_offered' => json_encode(['English']), // Default language
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
};
