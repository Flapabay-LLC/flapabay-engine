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
        // This migration is no longer needed as the listings table has been dropped
        // and merged into the listings table. These fields should be added to listings if needed.
        
        // No-op: Migration is disabled as listings table no longer exists
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['square_feet', 'children_guests', 'infant_guests', 'adult_guests', 'pet_guests']);
        });
    }
};