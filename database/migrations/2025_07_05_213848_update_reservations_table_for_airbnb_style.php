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
        Schema::table('reservations', function (Blueprint $table) {
            // Add new columns
            $table->integer('number_of_infants')->default(0)->after('number_of_children');
            $table->integer('number_of_pets')->default(0)->after('number_of_infants');
            $table->string('guest_phone')->nullable()->after('is_instant_booking');
            $table->string('guest_email')->nullable()->after('guest_phone');
            
            // Add property_id column
            $table->unsignedBigInteger('property_id')->nullable()->after('user_id');
            
            // Add foreign key constraint for property_id
            $table->foreign('property_id')->references('id')->on('properties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['property_id']);
            
            // Drop columns
            $table->dropColumn([
                'property_id',
                'number_of_infants',
                'number_of_pets',
                'guest_phone',
                'guest_email'
            ]);
        });
    }
};
