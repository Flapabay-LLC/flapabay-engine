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
        Schema::create('currencies', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 3)->unique(); // e.g., USD, EUR
            $table->string('name')->nullable();             // Currency name
            $table->string('country')->nullable();             // Currency name
            $table->string('symbol', 10)->nullable(); // e.g., $, €
            $table->boolean('is_active')->default(true); // Indicates if currency is supported
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
