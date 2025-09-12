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
        Schema::table('user_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('trip_id')->nullable()->after('listing_id'); // Foreign key for booking/trip
            $table->enum('status', ['draft', 'published'])->default('draft')->after('review');
            $table->text('host_response_comment')->nullable()->after('status');
            $table->timestamp('host_response_created_at')->nullable()->after('host_response_comment');
            
            // Add foreign key constraints
            $table->foreign('trip_id')->references('id')->on('bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_reviews', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->dropColumn(['trip_id', 'status', 'host_response_comment', 'host_response_created_at']);
        });
    }
};
