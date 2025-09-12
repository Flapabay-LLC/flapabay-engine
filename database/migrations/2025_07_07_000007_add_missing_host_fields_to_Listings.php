<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migration disabled: listings table has been dropped and merged into listings
        // All host fields are now managed through the listings table
        
        /*
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'every_bedroom_has_lock')) {
                $table->boolean('every_bedroom_has_lock')->nullable();
            }
            if (!Schema::hasColumn('listings', 'kind_of_bathrooms')) {
                $table->string('kind_of_bathrooms')->nullable();
            }
            if (!Schema::hasColumn('listings', 'who_is_there')) {
                $table->json('who_is_there')->nullable();
            }
            if (!Schema::hasColumn('listings', 'favourites')) {
                $table->json('favourites')->nullable();
            }
            if (!Schema::hasColumn('listings', 'safety_items')) {
                $table->string('safety_items')->nullable();
            }
            if (!Schema::hasColumn('listings', 'images')) {
                $table->json('images')->nullable();
            }
            if (!Schema::hasColumn('listings', 'features')) {
                $table->json('features')->nullable();
            }
            if (!Schema::hasColumn('listings', 'host_booking_settings')) {
                $table->json('host_booking_settings')->nullable();
            }
            if (!Schema::hasColumn('listings', 'who_to_welcome_first_reservation')) {
                $table->string('who_to_welcome_first_reservation')->nullable();
            }
            if (!Schema::hasColumn('listings', 'weekday_price')) {
                $table->decimal('weekday_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('listings', 'weekend_price')) {
                $table->decimal('weekend_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('listings', 'place_items')) {
                $table->json('place_items')->nullable();
            }
        });
        */
    }

    public function down(): void
    {
        // Migration disabled: listings table has been dropped and merged into listings
        // Rollback operations are no longer applicable
        
        /*
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'every_bedroom_has_lock',
                'kind_of_bathrooms',
                'who_is_there',
                'favourites',
                'safety_items',
                'images',
                'features',
                'host_booking_settings',
                'who_to_welcome_first_reservation',
                'weekday_price',
                'weekend_price',
                'place_items',
            ]);
        });
        */
    }
};