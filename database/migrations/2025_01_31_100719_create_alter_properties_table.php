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
        // This migration is no longer needed as the properties table has been dropped
        // and merged into the listings table. The relevant fields are now part of listings.
        
        // The listings table modifications are also not needed as the structure is already correct
        
        // No-op: Migration is disabled as properties table no longer exists
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alter_properties');
    }
};
