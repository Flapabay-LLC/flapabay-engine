<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            if (!Schema::hasColumn('user_details', 'my_work')) {
                $table->string('my_work')->nullable();
            }
            if (!Schema::hasColumn('user_details', 'favourite_place')) {
                $table->string('favourite_place')->nullable();
            }
            if (!Schema::hasColumn('user_details', 'my_fun_fact')) {
                $table->string('my_fun_fact')->nullable();
            }
            if (!Schema::hasColumn('user_details', 'pets')) {
                $table->json('pets')->nullable();
            }
            if (!Schema::hasColumn('user_details', 'show_decade_born')) {
                $table->boolean('show_decade_born')->default(false);
            }
            if (!Schema::hasColumn('user_details', 'shools_went_to')) {
                $table->json('shools_went_to')->nullable();
            }
            if (!Schema::hasColumn('user_details', 'favourite_songs')) {
                $table->json('favourite_songs')->nullable();
            }
            if (!Schema::hasColumn('user_details', 'spend_time_in')) {
                $table->json('spend_time_in')->nullable();
            }
            if (!Schema::hasColumn('user_details', 'most_useles_skill')) {
                $table->json('most_useles_skill')->nullable();
            }
            if (!Schema::hasColumn('user_details', 'am_obessed_with')) {
                $table->json('am_obessed_with')->nullable();
            }
            if (!Schema::hasColumn('user_details', 'boi_title')) {
                $table->string('boi_title')->nullable();
            }
            if (!Schema::hasColumn('user_details', 'know_where_been')) {
                $table->boolean('know_where_been')->default(false);
            }
            if (!Schema::hasColumn('user_details', 'my_interests')) {
                $table->json('my_interests')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn([
                'my_work',
                'favourite_place',
                'my_fun_fact',
                'pets',
                'show_decade_born',
                'shools_went_to',
                'favourite_songs',
                'spend_time_in',
                'most_useles_skill',
                'am_obessed_with',
                'boi_title',
                'know_where_been',
                'my_interests',
            ]);
        });
    }
}; 