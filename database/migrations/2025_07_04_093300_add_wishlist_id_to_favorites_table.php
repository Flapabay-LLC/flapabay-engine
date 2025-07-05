<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->foreignId('wishlist_id')->nullable()->constrained('wishlists')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropForeign(['wishlist_id']);
            $table->dropColumn('wishlist_id');
        });
    }
}; 