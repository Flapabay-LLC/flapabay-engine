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
        // Column renaming is no longer applicable
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Migration disabled: properties table has been dropped and merged into listings
        // Rollback operations are no longer applicable
    }
};
