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
        // This migration is no longer needed as the listings table has been dropped
        // and merged into the listings table. The is_draft field is now part of listings.
        
        // No-op: Migration is disabled as listings table no longer exists
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert string values back to boolean
        DB::table('listings')
            ->whereIn('is_draft', ['published', 'active'])
            ->update(['is_draft' => '1']);
            
        DB::table('listings')
            ->whereIn('is_draft', ['draft', 'pending', 'archived', 'inactive'])
            ->update(['is_draft' => '0']);

        // Change column back to boolean
        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('is_draft')->default(false)->change();
        });
    }
};