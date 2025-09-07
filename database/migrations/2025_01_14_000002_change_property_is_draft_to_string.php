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
        // First, change the column type to string
        Schema::table('properties', function (Blueprint $table) {
            $table->string('is_draft')->default('draft')->change();
        });
        
        // Then update existing boolean-like values to proper strings
        DB::table('properties')
            ->where('is_draft', '1')
            ->update(['is_draft' => 'published']);
            
        DB::table('properties')
            ->where('is_draft', '0')
            ->update(['is_draft' => 'draft']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert string values back to boolean
        DB::table('properties')
            ->whereIn('is_draft', ['published', 'active'])
            ->update(['is_draft' => '1']);
            
        DB::table('properties')
            ->whereIn('is_draft', ['draft', 'pending', 'archived', 'inactive'])
            ->update(['is_draft' => '0']);

        // Change column back to boolean
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('is_draft')->default(false)->change();
        });
    }
};