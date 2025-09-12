<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // This migration is no longer needed as listings have been merged into listings
        // and listing_id column has been removed from listings table.
        // The data migration was handled during the table structure changes.
        
        // No-op: Migration is disabled as the data structure has been consolidated
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible as it involves data transformation
        // In a real scenario, you would need to backup the original data first
        throw new Exception('This migration cannot be reversed automatically. Please restore from backup.');
    }
};