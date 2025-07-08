<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'every_bedroom_has_lock')) {
                $table->boolean('every_bedroom_has_lock')->nullable();
            }
            if (!Schema::hasColumn('properties', 'kind_of_bathrooms')) {
                $table->string('kind_of_bathrooms')->nullable();
            }
            if (!Schema::hasColumn('properties', 'who_is_there')) {
                $table->json('who_is_there')->nullable();
            }
            if (!Schema::hasColumn('properties', 'favourites')) {
                $table->json('favourites')->nullable();
            }
            if (!Schema::hasColumn('properties', 'safety_items')) {
                $table->string('safety_items')->nullable();
            }
            if (!Schema::hasColumn('properties', 'images')) {
                $table->json('images')->nullable();
            }
            if (!Schema::hasColumn('properties', 'features')) {
                $table->json('features')->nullable();
            }
            if (!Schema::hasColumn('properties', 'host_booking_settings')) {
                $table->json('host_booking_settings')->nullable();
            }
            if (!Schema::hasColumn('properties', 'who_to_welcome_first_reservation')) {
                $table->string('who_to_welcome_first_reservation')->nullable();
            }
            if (!Schema::hasColumn('properties', 'weekday_price')) {
                $table->decimal('weekday_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('properties', 'weekend_price')) {
                $table->decimal('weekend_price', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('properties', 'place_items')) {
                $table->json('place_items')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
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
    }
}; 