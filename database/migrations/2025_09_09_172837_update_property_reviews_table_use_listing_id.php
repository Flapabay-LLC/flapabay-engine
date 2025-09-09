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
        // Only perform migration if listing_id column exists
        if (Schema::hasColumn('property_reviews', 'listing_id')) {
            Schema::table('property_reviews', function (Blueprint $table) {
                // Drop the existing foreign key constraint
                try {
                    $table->dropForeign(['listing_id']);
                } catch (Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                
                // Rename the column from listing_id to listing_id
                $table->renameColumn('listing_id', 'listing_id');
            });
        }
        
        // Add foreign key constraint if listing_id column exists and doesn't have constraint
        if (Schema::hasColumn('property_reviews', 'listing_id')) {
            // Check if foreign key constraint doesn't already exist
            $foreignKeyExists = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'property_reviews' 
                AND COLUMN_NAME = 'listing_id' 
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ")[0]->count > 0;
            
            if (!$foreignKeyExists) {
                Schema::table('property_reviews', function (Blueprint $table) {
                    $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_reviews', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['listing_id']);
            
            // Rename the column back from listing_id to listing_id
            $table->renameColumn('listing_id', 'listing_id');
            
            // Add back the original foreign key constraint
            $table->foreign('listing_id')->references('id')->on('properties')->onDelete('cascade');
        });
    }
};
