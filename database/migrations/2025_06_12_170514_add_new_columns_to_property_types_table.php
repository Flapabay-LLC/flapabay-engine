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
        Schema::table('property_types', function (Blueprint $table) {
            $table->dropColumn('icon');
            $table->string('black_icon')->after('name');
            $table->string('white_icon')->after('black_icon');
            $table->string('bg_color')->after('description');
            $table->string('color')->after('bg_color');
            $table->string('type')->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('property_types', function (Blueprint $table) {
            $table->string('icon')->after('name');
            $table->dropColumn(['black_icon', 'white_icon', 'bg_color', 'color', 'type']);
        });
    }
};
