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
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('title');
        });
        Schema::table('listings', function (Blueprint $table) {
            $table->string('title')->nullable()->after('host_id');
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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('title')->nullable();
        });
    }
}; 