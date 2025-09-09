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
        // This migration is no longer needed as the stays_details and experiences_details
        // tables are created later (September 2025) with the correct foreign key constraints.
        // The foreign keys are properly set up in their respective creation migrations.
        
        // No-op: Migration is disabled as the table structure is handled in creation migrations
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraints
        Schema::table('stays_details', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
        });
        
        Schema::table('experiences_details', function (Blueprint $table) {
            $table->dropForeign(['listing_id']);
        });
        
        // Re-add the original foreign key constraints
        Schema::table('stays_details', function (Blueprint $table) {
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
        });
        
        Schema::table('experiences_details', function (Blueprint $table) {
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
        });
    }
};