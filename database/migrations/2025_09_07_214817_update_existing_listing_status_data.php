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
        // Migration disabled: listings table has been dropped and merged into listings
        // Data update is no longer applicable as the source table doesn't exist
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for data cleanup
    }
};
