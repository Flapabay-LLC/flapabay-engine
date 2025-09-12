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
        Schema::create('listings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('title', 100)->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('description')->nullable(); // Updated to text for longer descriptions
            $table->string('location', 150)->nullable();

            $table->string('address')->nullable();
            $table->string('county')->nullable();
            $table->decimal('latitude', 10, 7)->nullable(); // Store latitude with decimal precision
            $table->decimal('longitude', 10, 7)->nullable(); // Store longitude with decimal precision

            $table->time('check_in_hour')->nullable(); // Changed to 'time' type for better validation
            $table->time('check_out_hour')->nullable(); // Changed to 'time' type for better validation

            // Additional columns from seeder
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->boolean('cancellation_policy')->default(false);
            $table->boolean('is_completed')->default(false);
            $table->string('listing_type')->nullable();
            $table->string('availability_type')->nullable();
            $table->string('flexible_month')->nullable();
            $table->unsignedBigInteger('listing_type_id')->nullable();
            $table->integer('num_of_bedrooms')->nullable();
            $table->integer('num_of_bathrooms')->nullable();
            $table->integer('num_of_quarters')->nullable();
            $table->boolean('has_unallocated_rooms')->default(false);

            $table->integer('num_of_guests')->nullable();
            $table->integer('num_of_children')->nullable();
            $table->integer('maximum_guests')->nullable();
            $table->boolean('allow_extra_guests')->default(false);
            $table->string('neighborhood_area', 100)->nullable();
            $table->string('country', 100)->nullable();

            $table->boolean('show_contact_form_instead_of_booking')->default(false);
            $table->boolean('allow_instant_booking')->default(false);
            $table->string('currency', 3)->default('USD'); // Changed to string to store currency like USD, EUR, etc.

            $table->json('price_range')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('price_per_night', 10, 2)->nullable();
            $table->decimal('weekday_price', 10, 2)->nullable();
            $table->decimal('weekend_price', 10, 2)->nullable();
            $table->decimal('additional_guest_price', 10, 2)->nullable();
            $table->decimal('children_price', 10, 2)->nullable(); // Fixed extra space in the column name

            $table->json('amenities')->nullable();
            $table->json('house_rules')->nullable();
            $table->json('who_is_there')->nullable();
            $table->json('place_items')->nullable();

            $table->integer('page')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->boolean('favorite')->default(false);
            $table->json('images')->nullable();
            $table->json('video_link')->nullable();
            $table->boolean('verified')->default(false);
            $table->text('about_place')->nullable();
            $table->string('host_type')->nullable();
            $table->string('featured_status')->nullable();
            $table->string('kind_of_bathrooms')->nullable();
            $table->boolean('every_bedroom_has_lock')->default(false);

            $table->boolean('is_draft')->nullable()->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
