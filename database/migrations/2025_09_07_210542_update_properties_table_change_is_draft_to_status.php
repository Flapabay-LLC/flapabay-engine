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
        // Use raw SQL to rename column to avoid Laravel's default value issues
        \DB::statement('ALTER TABLE properties CHANGE is_draft status VARCHAR(255)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Use raw SQL to rename column back
        \DB::statement('ALTER TABLE properties CHANGE status is_draft VARCHAR(255)');
    }
};
