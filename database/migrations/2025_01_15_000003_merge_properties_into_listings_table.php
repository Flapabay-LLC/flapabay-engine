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
        Schema::table('listings', function (Blueprint $table) {
            // Remove old listing_id and user_id columns
            $table->dropColumn(['listing_id', 'user_id']);
            
            // Add user_id for the owner/host
            $table->unsignedBigInteger('user_id')->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add all property fields from properties table
            $table->string('title', 100)->nullable()->after('user_id');
            $table->text('description')->nullable()->after('title');
            $table->string('location', 150)->nullable()->after('description');
            $table->string('address')->nullable()->after('location');
            $table->string('county')->nullable()->after('address');
            $table->decimal('latitude', 10, 7)->nullable()->after('county');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            
            // Time fields
            $table->time('check_in_hour')->nullable()->after('longitude');
            $table->time('check_out_hour')->nullable()->after('check_in_hour');
            
            // Guest capacity fields
            $table->integer('num_of_guests')->nullable()->after('check_out_hour');
            $table->integer('num_of_children')->nullable()->after('num_of_guests');
            $table->integer('maximum_guests')->nullable()->after('num_of_children');
            $table->boolean('allow_extra_guests')->default(false)->after('maximum_guests');
            
            // Location details
            $table->string('neighborhood_area', 100)->nullable()->after('allow_extra_guests');
            $table->string('country', 100)->nullable()->after('neighborhood_area');
            
            // Booking settings
            $table->boolean('show_contact_form_instead_of_booking')->default(false)->after('country');
            $table->boolean('allow_instant_booking')->default(false)->after('show_contact_form_instead_of_booking');
            
            // Pricing fields
            $table->string('currency', 3)->default('USD')->after('allow_instant_booking');
            $table->json('price_range')->nullable()->after('currency');
            $table->decimal('price', 10, 2)->nullable()->after('price_range');
            $table->decimal('price_per_night', 10, 2)->nullable()->after('price');
            $table->decimal('additional_guest_price', 10, 2)->nullable()->after('price_per_night');
            $table->decimal('children_price', 10, 2)->nullable()->after('additional_guest_price');
            $table->decimal('weekday_price', 10, 2)->nullable()->after('children_price');
            $table->decimal('weekend_price', 10, 2)->nullable()->after('weekday_price');
            
            // Property details
            $table->json('amenities')->nullable()->after('weekend_price');
            $table->json('house_rules')->nullable()->after('amenities');
            $table->integer('page')->nullable()->after('house_rules');
            $table->decimal('rating', 3, 2)->nullable()->after('page');
            $table->boolean('favorite')->default(false)->after('rating');
            $table->json('images')->nullable()->after('favorite');
            $table->json('video_link')->nullable()->after('images');
            $table->boolean('verified')->default(false)->after('video_link');
            
            // Property type and categorization
            $table->enum('property_type', ['Featured', 'Guest Favorite', 'Others'])->nullable()->after('verified');
            $table->string('featured_status')->nullable()->after('property_type'); // enum: null, 'guest_favourite', 'featured'
            $table->unsignedBigInteger('property_type_id')->nullable()->after('featured_status');
            $table->string('listing_type')->nullable()->after('property_type_id'); // stay, experience
            
            // Additional property fields from the extended properties table
            $table->boolean('has_unallocated_rooms')->default(false)->after('listing_type');
            $table->integer('num_of_bedrooms')->nullable()->after('has_unallocated_rooms');
            $table->integer('num_of_bathrooms')->nullable()->after('num_of_bedrooms');
            $table->integer('num_of_quarters')->nullable()->after('num_of_bathrooms');
            $table->integer('children_guests')->nullable()->after('num_of_quarters');
            $table->integer('infant_guests')->nullable()->after('children_guests');
            $table->integer('adult_guests')->nullable()->after('infant_guests');
            $table->integer('pet_guests')->nullable()->after('adult_guests');
            
            // Host and property details
            $table->text('about_place')->nullable()->after('pet_guests');
            $table->string('host_type')->nullable()->after('about_place'); // enum
            $table->string('occupation_type')->nullable()->after('host_type'); // enum
            $table->string('street')->nullable()->after('occupation_type');
            $table->string('city')->nullable()->after('street');
            $table->string('state')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('state');
            $table->integer('square_feet')->nullable()->after('zip_code');
            
            // Booking details
            $table->json('place_items')->nullable()->after('square_feet');
            $table->integer('nights')->nullable()->after('place_items');
            $table->date('check_in_date')->nullable()->after('nights');
            $table->date('check_out_date')->nullable()->after('check_in_date');
            
            // Property characteristics
            $table->string('type_of_place')->nullable()->after('check_out_date');
            $table->json('coordinates')->nullable()->after('type_of_place');
            $table->boolean('every_bedroom_has_lock')->default(false)->after('coordinates');
            $table->string('kind_of_bathrooms')->nullable()->after('every_bedroom_has_lock');
            $table->json('who_is_there')->nullable()->after('kind_of_bathrooms');
            
            // Update existing status column to match property status
            $table->string('status')->default('draft')->change(); // draft, published, pending, archived
            
            // Update published_at to be nullable datetime
            $table->datetime('published_at')->nullable()->change();
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
                'property_type', 'featured_status', 'property_type_id', 'listing_type',
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