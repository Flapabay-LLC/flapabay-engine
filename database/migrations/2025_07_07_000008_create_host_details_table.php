<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('host_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('type_of_place')->nullable();
            $table->json('address')->nullable();
            $table->json('coordinates')->nullable();
            $table->integer('guests')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('beds')->nullable();
            $table->boolean('every_bedroom_has_lock')->nullable();
            $table->string('kind_of_bathrooms')->nullable();
            $table->json('who_is_there')->nullable();
            $table->json('amenities')->nullable();
            $table->json('favourites')->nullable();
            $table->json('safety_items')->nullable();
            $table->json('images')->nullable();
            $table->string('title')->nullable();
            $table->json('features')->nullable();
            $table->text('description')->nullable();
            $table->json('host_booking_settings')->nullable();
            $table->string('who_to_welcome_first_reservation')->nullable();
            $table->decimal('weekday_price', 10, 2)->nullable();
            $table->decimal('weekend_price', 10, 2)->nullable();
            $table->json('discounts')->nullable();
            $table->json('place_items')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('host_details');
    }
}; 