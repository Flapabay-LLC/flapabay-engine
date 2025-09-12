<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migration disabled: listings table structure is already correct
        // The listings table was created with the proper structure from the start
        // No additional modifications needed at this time
        
        // Add user_id for the owner/host (only if it doesn't exist)
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'user_id')) {
                $table->unsignedBigInteger('user_id')->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            // Remove all the added columns
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'user_id', 'title', 'description', 'location', 'address', 'county',
                'latitude', 'longitude', 'check_in_hour', 'check_out_hour',
                'num_of_guests', 'num_of_children', 'maximum_guests', 'allow_extra_guests',
                'neighborhood_area', 'country', 'show_contact_form_instead_of_booking',
                'allow_instant_booking', 'currency', 'price_range', 'price',
                'price_per_night', 'additional_guest_price', 'children_price',
                'weekday_price', 'weekend_price', 'amenities', 'house_rules',
                'page', 'rating', 'favorite', 'images', 'video_link', 'verified',
                'listing_type', 'featured_status', 'listing_type_id', 'listing_type',
                'has_unallocated_rooms', 'num_of_bedrooms', 'num_of_bathrooms',
                'num_of_quarters', 'children_guests', 'infant_guests', 'adult_guests',
                'pet_guests', 'about_place', 'host_type', 'occupation_type',
                'street', 'city', 'state', 'zip_code', 'square_feet',
                'place_items', 'nights', 'check_in_date', 'check_out_date',
                'type_of_place', 'coordinates', 'every_bedroom_has_lock',
                'kind_of_bathrooms', 'who_is_there'
            ]);
            
            // Restore original columns
            $table->unsignedBigInteger('listing_id')->after('id');
            $table->unsignedBigInteger('user_id')->after('listing_id');
            $table->boolean('status')->default(false)->change();
            $table->date('published_at')->nullable()->change();
        });
    }
};