<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('co_hosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('co_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('listing_id')->constrained('listings')->onDelete('cascade');
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
            $table->json('permissions')->nullable(); // Store permissions as JSON
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Unique constraint to prevent duplicate co-host assignments
            $table->unique(['user_id', 'co_user_id', 'listing_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('co_hosts');
    }
};