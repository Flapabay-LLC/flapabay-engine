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
        // Step 1: Add user_id column and is_host column (only if they don't exist)
        if (!Schema::hasColumn('properties', 'user_id')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
        
        if (!Schema::hasColumn('properties', 'is_host')) {
            Schema::table('properties', function (Blueprint $table) {
                $table->boolean('is_host')->default(false)->after('user_id');
            });
        }
        
        // Migrate existing data: copy host_id to user_id and set is_host to true where host_id exists
        // First, clean up invalid host_id values that don't exist in users table
        DB::statement('UPDATE properties SET host_id = NULL WHERE host_id IS NOT NULL AND host_id NOT IN (SELECT id FROM users)');
        
        // Copy host_id to user_id and convert host_id to boolean
        DB::statement('UPDATE properties SET user_id = COALESCE(host_id, 1), is_host = CASE WHEN host_id IS NOT NULL THEN true ELSE false END');
        
        Schema::table('properties', function (Blueprint $table) {
            // Drop the old host_id column
            $table->dropColumn('host_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Re-add host_id column
            $table->unsignedBigInteger('host_id')->nullable()->after('id');
            
            // Migrate data back: copy user_id to host_id where is_host is true
            DB::statement('UPDATE properties SET host_id = CASE WHEN is_host = true THEN user_id ELSE NULL END');
            
            // Drop the new columns
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_host']);
        });
    }
};