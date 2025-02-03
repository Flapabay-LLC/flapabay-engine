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
        Schema::table('properties', function (Blueprint $table) {
            $table->enum('occupation_type',['room','entire_place','shared_room_in_hotel'])->nullable();
            $table->text('about_place')->nullable()->after('description');
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->boolean('has_unallocated_rooms')->default(false);
            $table->integer('num_of_bedrooms')->default(0);
            $table->integer('num_of_bathrooms')->default(0);
            $table->integer('num_of_quarters')->default(0);
            $table->string('first_reserver')->nullable();
            $table->json('place_items')->nullable()->after('amenities');
            $table->enum('host_type',['Private Individual','Business'])->nullable()->after('about_place');

            $table->removeColumn('favorite');
        });

        Schema::table('listings', function (Blueprint $table) {
            $table->removeColumn('category_id');
            $table->string('listing_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alter_properties');
    }
};
