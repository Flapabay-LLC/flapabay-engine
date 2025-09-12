<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migration disabled: listings table has been dropped and merged into listings
        // All host fields are now managed through the listings table
    }

    public function down(): void
    {
        // Migration disabled: listings table has been dropped and merged into listings
        // Rollback operations are no longer applicable
        
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'description',
                'features',
                'images',
            ]);
        });
    }
};