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
        // Remove chat_id from messages if it exists
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'chat_id')) {
                $table->dropForeign(['chat_id']);
                $table->dropColumn('chat_id');
            }
        });

        // Now drop the chats table if it exists
        Schema::dropIfExists('chats');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the chats table (structure only, no data restoration)
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user1_id');
            $table->unsignedBigInteger('user2_id');
            $table->timestamps();
        });

        // Add chat_id back to messages (no foreign key restoration)
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('chat_id')->nullable();
        });
    }
}; 