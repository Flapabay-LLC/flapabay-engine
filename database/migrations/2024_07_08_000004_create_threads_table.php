<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guest_id');
            $table->unsignedBigInteger('user_id');
            $table->string('thread_type')->nullable(); // inquiry, reservation, follow-up
            $table->string('context_type')->nullable(); // listing, booking
            $table->unsignedBigInteger('context_id')->nullable(); // listing_id or booking_id
            $table->string('status')->default('active'); // active, resolved, archived
            $table->string('category')->nullable(); // Homes, Experiences, Traveling, Support
            $table->timestamps();

            $table->foreign('guest_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['guest_id', 'user_id', 'context_type', 'context_id'], 'unique_thread_per_context');
        });
    }

    public function down(): void
    {
        Schema::table('threads', function (Blueprint $table) {
            $table->dropColumn('category');
        });
        Schema::dropIfExists('threads');
    }
}; 