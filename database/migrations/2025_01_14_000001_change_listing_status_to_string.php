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
        Schema::table('listings', function (Blueprint $table) {
            $table->string('status')->default('draft')->change();
        });
        
        // Then update existing boolean-like values to proper strings
        DB::table('listings')
            ->where('status', '1')
            ->update(['status' => 'published']);
            
        DB::table('listings')
            ->where('status', '0')
            ->update(['status' => 'draft']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert string values back to boolean
        DB::table('listings')
            ->whereIn('status', ['published', 'active'])
            ->update(['status' => '1']);
            
        DB::table('listings')
            ->whereIn('status', ['draft', 'pending', 'archived', 'inactive'])
            ->update(['status' => '0']);

        // Change column back to boolean
        Schema::table('listings', function (Blueprint $table) {
            $table->boolean('status')->default(false)->change();
        });
    }
};