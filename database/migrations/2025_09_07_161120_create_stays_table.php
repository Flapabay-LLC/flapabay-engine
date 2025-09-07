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
        Schema::create('stays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            
            // Stay-specific fields
            $table->decimal('cleaning_fee', 10, 2)->nullable();
            $table->integer('minimum_nights')->default(1);
            $table->integer('maximum_nights')->nullable();
            $table->decimal('security_deposit', 10, 2)->nullable();
            $table->decimal('weekend_price_multiplier', 3, 2)->default(1.00);
            $table->decimal('monthly_discount_percentage', 5, 2)->nullable();
            $table->decimal('weekly_discount_percentage', 5, 2)->nullable();
            
            // Check-in/out policies
            $table->time('earliest_check_in')->nullable();
            $table->time('latest_check_in')->nullable();
            $table->time('check_out_time')->nullable();
            $table->boolean('self_check_in')->default(false);
            $table->text('check_in_instructions')->nullable();
            
            // Booking policies
            $table->integer('advance_booking_days')->nullable(); // How many days in advance can book
            $table->integer('preparation_time_hours')->default(0); // Hours needed between bookings
            $table->boolean('instant_book_eligible')->default(false);
            
            // Additional fees
            $table->decimal('pet_fee', 10, 2)->nullable();
            $table->decimal('extra_guest_fee', 10, 2)->nullable();
            $table->integer('extra_guest_threshold')->nullable(); // After how many guests the fee applies
            
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
        Schema::dropIfExists('stays');
    }
};
