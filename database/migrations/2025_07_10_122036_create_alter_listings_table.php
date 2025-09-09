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
        // Migration disabled: properties table has been dropped and merged into listings
        // Only keeping the listings table modification
        
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'title')) {
                $table->string('title')->nullable()->after('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('title');
        });
        // Migration disabled: properties table has been dropped and merged into listings
        // Rollback operations for properties table are no longer applicable
    }
};