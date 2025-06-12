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
        Schema::table('user_details', function (Blueprint $table) {
            $table->json('my_interests')->nullable()->after('youtube');
            $table->string('know_where_been')->nullable();
            $table->string('boi_title')->nullable();
            $table->json('am_obessed_with')->nullable();
            $table->json('most_useles_skill')->nullable();
            $table->json('spend_time_in')->nullable();
            $table->json('favourite_songs')->nullable();
            $table->json('shools_went_to')->nullable();
            $table->string('show_decade_born')->nullable();
            $table->json('pets')->nullable();
            $table->text('my_fun_fact')->nullable();
            $table->string('favourite_place')->nullable();
            $table->string('my_work')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn([
                'my_interests',
                'know_where_been',
                'boi_title',
                'am_obessed_with',
                'most_useles_skill',
                'spend_time_in',
                'favourite_songs',
                'shools_went_to',
                'show_decade_born',
                'pets',
                'my_fun_fact',
                'favourite_place',
                'my_work'
            ]);
        });
    }
}; 