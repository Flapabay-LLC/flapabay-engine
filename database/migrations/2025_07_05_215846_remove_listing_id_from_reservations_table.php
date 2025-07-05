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
        Schema::table('reservations', function (Blueprint $table) {
            // Drop the listing_id column and its foreign key constraint
            $table->dropForeign(['listing_id']);
            $table->dropColumn('listing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            // Re-add the listing_id column
            $table->unsignedBigInteger('listing_id')->after('user_id');
            $table->foreign('listing_id')->references('id')->on('listings')->onDelete('cascade');
        });
    }
};
