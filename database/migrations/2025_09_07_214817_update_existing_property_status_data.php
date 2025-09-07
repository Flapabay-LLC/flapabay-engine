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
        // Update any properties that might have invalid status values
        // Convert any boolean-like values to proper string status
        \DB::statement("
            UPDATE properties 
            SET status = CASE 
                WHEN status IN ('1', 'true', '0', 'false') THEN 'draft'
                WHEN status NOT IN ('draft', 'published', 'pending', 'archived') THEN 'draft'
                ELSE status 
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for data cleanup
    }
};
