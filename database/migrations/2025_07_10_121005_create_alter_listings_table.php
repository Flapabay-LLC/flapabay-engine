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
        // Migration disabled: listings table structure is already correct
        // These fields can be added later if needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'availability_type')) {
                $table->dropColumn('availability_type');
            }
            if (Schema::hasColumn('listings', 'flexible_period')) {
                $table->dropColumn('flexible_period');
            }
            if (Schema::hasColumn('listings', 'flexible_month')) {
                $table->dropColumn('flexible_month');
            }
        });
    }
};