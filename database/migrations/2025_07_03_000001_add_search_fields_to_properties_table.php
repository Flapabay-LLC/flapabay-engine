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
            $table->integer('square_feet')->nullable()->after('num_of_quarters');
            $table->integer('children_guests')->nullable()->after('square_feet');
            $table->integer('infant_guests')->nullable()->after('children_guests');
            $table->integer('adult_guests')->nullable()->after('infant_guests');
            $table->integer('pet_guests')->nullable()->after('adult_guests');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['square_feet', 'children_guests', 'infant_guests', 'adult_guests', 'pet_guests']);
        });
    }
}; 