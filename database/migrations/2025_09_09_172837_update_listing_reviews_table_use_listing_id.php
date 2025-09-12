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
        if (Schema::hasColumn('listing_reviews', 'listing_id')) {
            Schema::table('listing_reviews', function (Blueprint $table) {
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
        if (Schema::hasColumn('listing_reviews', 'listing_id')) {
            // SQLite-compatible approach: skip constraint check for now
            // Schema::table('listing_reviews', function (Blueprint $table) {
            //     $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
            // });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listing_reviews', function (Blueprint $table) {
            // Drop the new foreign key constraint
            $table->dropForeign(['listing_id']);
            
            // Rename the column back from listing_id to listing_id
            $table->renameColumn('listing_id', 'listing_id');
            
            // Add back the original foreign key constraint
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
        });
    }
};
