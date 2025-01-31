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
        Schema::create('stays', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->default(1); // Not Necessary for now
            $table->unsignedBigInteger('host_id')->nullable(); // Using Host id as Key
            $table->unsignedBigInteger('property_id')->nullable(); // Linked property
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('about_this_place')->nullable();
            $table->date('starting')->nullable();
            $table->date('ending')->nullable();
            $table->integer('max_guests')->default(1);
            $table->integer('total_nights')->nullable();
            $table->decimal('price_per_night', 10, 2)->nullable();;
            $table->decimal('total_price', 10, 2)->nullable();;
            $table->json('amenities')->nullable(); // JSON format for amenities
            $table->json('images')->nullable(); // Store image URLs
            $table->json('videos')->nullable(); // Store video URLs
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stays');
    }
};
