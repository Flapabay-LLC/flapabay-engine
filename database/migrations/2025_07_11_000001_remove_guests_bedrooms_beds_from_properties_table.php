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
            if (Schema::hasColumn('properties', 'guests')) {
                $table->dropColumn('guests');
            }
            if (Schema::hasColumn('properties', 'bedrooms')) {
                $table->dropColumn('bedrooms');
            }
            if (Schema::hasColumn('properties', 'beds')) {
                $table->dropColumn('beds');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->integer('guests')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('beds')->nullable();
        });
    }
}; 