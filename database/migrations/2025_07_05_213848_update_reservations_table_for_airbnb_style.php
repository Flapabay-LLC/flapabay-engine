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
            
            // Add listing_id column only if it doesn't exist
            if (!Schema::hasColumn('reservations', 'listing_id')) {
                $table->unsignedBigInteger('listing_id')->nullable()->after('user_id');
                $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['listing_id']);
            
            // Drop columns
            $table->dropColumn([
                'listing_id',
                'number_of_infants',
                'number_of_pets',
                'guest_phone',
                'guest_email'
            ]);
        });
    }
};
