<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'type_of_place')) {
                $table->string('type_of_place')->nullable();
            }
            if (!Schema::hasColumn('properties', 'address')) {
                $table->json('address')->nullable();
            }
            if (!Schema::hasColumn('properties', 'coordinates')) {
                $table->json('coordinates')->nullable();
            }
            if (!Schema::hasColumn('properties', 'guests')) {
                $table->integer('guests')->nullable();
            }
            if (!Schema::hasColumn('properties', 'bedrooms')) {
                $table->integer('bedrooms')->nullable();
            }
            if (!Schema::hasColumn('properties', 'beds')) {
                $table->integer('beds')->nullable();
            }
            if (!Schema::hasColumn('properties', 'every_bedroom_has_lock')) {
                $table->boolean('every_bedroom_has_lock')->nullable();
            }
            if (!Schema::hasColumn('properties', 'kind_of_bathrooms')) {
                $table->string('kind_of_bathrooms')->nullable();
            }
            if (!Schema::hasColumn('properties', 'who_is_there')) {
                $table->json('who_is_there')->nullable();
            }
            if (!Schema::hasColumn('properties', 'amenities')) {
                $table->json('amenities')->nullable();
            }
            if (!Schema::hasColumn('properties', 'favourites')) {
                $table->json('favourites')->nullable();
            }
            if (!Schema::hasColumn('properties', 'safety_items')) {
                $table->json('safety_items')->nullable();
            }
            if (!Schema::hasColumn('properties', 'images')) {
                $table->json('images')->nullable();
            }
            if (!Schema::hasColumn('properties', 'title')) {
                $table->string('title')->nullable();
            }
            if (!Schema::hasColumn('properties', 'features')) {
                $table->json('features')->nullable();
            }
            if (!Schema::hasColumn('properties', 'description')) {
                $table->text('description')->nullable();
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
            if (!Schema::hasColumn('properties', 'discounts')) {
                $table->json('discounts')->nullable();
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
                'type_of_place',
                'address',
                'coordinates',
                'guests',
                'bedrooms',
                'beds',
                'every_bedroom_has_lock',
                'kind_of_bathrooms',
                'who_is_there',
                'amenities',
                'favourites',
                'safety_items',
                'images',
                'title',
                'features',
                'description',
                'host_booking_settings',
                'who_to_welcome_first_reservation',
                'weekday_price',
                'weekend_price',
                'discounts',
                'place_items',
            ]);
        });
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'description',
                'features',
                'images',
            ]);
        });
    }
}; 