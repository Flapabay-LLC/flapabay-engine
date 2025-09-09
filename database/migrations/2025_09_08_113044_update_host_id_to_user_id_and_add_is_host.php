<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add is_host boolean to users table (if it doesn't exist)
        if (!Schema::hasColumn('users', 'is_host')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_host')->default(false);
            });
        }

        // Update existing users who have user_id to set is_host = true (if user_id column exists)
        if (Schema::hasColumn('users', 'user_id')) {
            DB::statement('UPDATE users SET is_host = 1 WHERE user_id IS NOT NULL');
        }

        // Update threads table: rename user_id to user_id (if user_id exists)
        if (Schema::hasColumn('threads', 'user_id')) {
            Schema::table('threads', function (Blueprint $table) {
                // Try to drop foreign key if it exists
                try {
                    $table->dropForeign(['user_id']);
                } catch (Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $table->renameColumn('user_id', 'user_id');
            });
        }
        
        // Add foreign key constraint if user_id column exists and doesn't have constraint
        if (Schema::hasColumn('threads', 'user_id')) {
            Schema::table('threads', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Update saved_replies table: rename user_id to user_id (if user_id exists)
        if (Schema::hasColumn('saved_replies', 'user_id')) {
            Schema::table('saved_replies', function (Blueprint $table) {
                // Try to drop foreign key if it exists
                try {
                    $table->dropForeign(['user_id']);
                } catch (Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $table->renameColumn('user_id', 'user_id');
            });
        }
        
        // Add foreign key constraint if user_id column exists
        if (Schema::hasColumn('saved_replies', 'user_id')) {
            Schema::table('saved_replies', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Update listings table: rename user_id to user_id (if user_id exists)
        if (Schema::hasColumn('listings', 'user_id')) {
            Schema::table('listings', function (Blueprint $table) {
                $table->renameColumn('user_id', 'user_id');
            });
        }

        // Update co_hosts table: rename user_id to user_id (if user_id exists)
        if (Schema::hasColumn('co_hosts', 'user_id')) {
            // Drop foreign key constraint if it exists
            $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'co_hosts' AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME IS NOT NULL");
            if (!empty($foreignKeys)) {
                DB::statement("ALTER TABLE co_hosts DROP FOREIGN KEY {$foreignKeys[0]->CONSTRAINT_NAME}");
            }
            
            // Drop unique constraint if it exists (check for both listing_id and listing_id variants)
            $uniqueConstraints = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'co_hosts' AND CONSTRAINT_TYPE = 'UNIQUE' AND (CONSTRAINT_NAME LIKE '%user_id%' OR CONSTRAINT_NAME LIKE '%listing_id%' OR CONSTRAINT_NAME LIKE '%listing_id%')");
            if (!empty($uniqueConstraints)) {
                DB::statement("ALTER TABLE co_hosts DROP INDEX {$uniqueConstraints[0]->CONSTRAINT_NAME}");
            }
            
            Schema::table('co_hosts', function (Blueprint $table) {
                $table->renameColumn('user_id', 'user_id');
            });
        }
        
        // Add constraints if user_id column exists
        if (Schema::hasColumn('co_hosts', 'user_id')) {
            Schema::table('co_hosts', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                // Use listing_id instead of listing_id as that's the actual column name
                if (Schema::hasColumn('co_hosts', 'listing_id')) {
                    $table->unique(['user_id', 'co_user_id', 'listing_id']);
                } else {
                    $table->unique(['user_id', 'co_user_id']);
                }
            });
        }

        // Finally, drop user_id column from users table (if it exists)
        if (Schema::hasColumn('users', 'user_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back user_id column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
        });

        // Restore user_id values from is_host boolean (generate new user_ids)
        $users = DB::table('users')->where('is_host', true)->get();
        foreach ($users as $user) {
            do {
                $hostId = str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            } while (DB::table('users')->where('user_id', $hostId)->exists());
            
            DB::table('users')->where('id', $user->id)->update(['user_id' => $hostId]);
        }

        // Revert co_hosts table: rename user_id back to user_id
        Schema::table('co_hosts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropUnique(['user_id', 'co_user_id', 'listing_id']);
            $table->renameColumn('user_id', 'user_id');
        });
        
        Schema::table('co_hosts', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'co_user_id', 'listing_id']);
        });

        // Revert listings table: rename user_id back to user_id
        Schema::table('listings', function (Blueprint $table) {
            $table->renameColumn('user_id', 'user_id');
        });

        // Revert saved_replies table: rename user_id back to user_id
        Schema::table('saved_replies', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'user_id');
        });
        
        Schema::table('saved_replies', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Revert threads table: rename user_id back to user_id
        Schema::table('threads', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->renameColumn('user_id', 'user_id');
        });
        
        Schema::table('threads', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Remove is_host column from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_host');
        });
    }
};
