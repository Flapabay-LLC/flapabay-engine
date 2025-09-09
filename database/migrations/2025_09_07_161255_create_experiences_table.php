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
        Schema::create('experiences_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            
            // Experience-specific fields
            $table->integer('duration_hours')->nullable(); // Duration in hours
            $table->integer('duration_minutes')->nullable(); // Additional minutes
            $table->integer('minimum_group_size')->default(1);
            $table->integer('maximum_group_size')->nullable();
            $table->enum('activity_type', ['outdoor', 'indoor', 'cultural', 'adventure', 'food_drink', 'wellness', 'educational', 'entertainment'])->nullable();
            $table->enum('difficulty_level', ['easy', 'moderate', 'challenging', 'expert'])->nullable();
            $table->enum('physical_activity_level', ['low', 'moderate', 'high', 'very_high'])->nullable();
            
            // Age restrictions
            $table->integer('minimum_age')->nullable();
            $table->integer('maximum_age')->nullable();
            $table->boolean('children_allowed')->default(true);
            $table->text('age_restrictions_note')->nullable();
            
            // Scheduling
            $table->json('available_times')->nullable(); // Array of available time slots
            $table->json('available_days')->nullable(); // Days of week available
            $table->boolean('flexible_scheduling')->default(false);
            $table->integer('advance_booking_hours')->default(24); // Hours in advance needed
            $table->integer('cancellation_hours')->default(24); // Hours before cancellation allowed
            
            // Experience details
            $table->text('what_to_bring')->nullable();
            $table->text('what_is_included')->nullable();
            $table->text('what_is_not_included')->nullable();
            $table->text('meeting_point')->nullable();
            $table->text('safety_requirements')->nullable();
            $table->json('languages_offered')->nullable(); // Languages the experience is offered in
            
            // Pricing
            $table->decimal('price_per_person', 10, 2)->nullable();
            $table->decimal('group_discount_percentage', 5, 2)->nullable();
            $table->integer('group_discount_threshold')->nullable(); // Minimum group size for discount
            
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
            $table->unique('listing_id'); // One-to-one relationship
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiences_details');
    }
};
