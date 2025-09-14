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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0.00)->after('currency'); // Current available balance
            $table->decimal('pending_earnings', 15, 2)->default(0.00)->after('balance'); // Earnings not yet available for withdrawal
            $table->decimal('total_earnings', 15, 2)->default(0.00)->after('pending_earnings'); // Lifetime total earnings
            $table->decimal('total_withdrawn', 15, 2)->default(0.00)->after('total_earnings'); // Total amount withdrawn
            $table->timestamp('last_payout_at')->nullable()->after('total_withdrawn'); // Last payout date
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'balance',
                'pending_earnings', 
                'total_earnings',
                'total_withdrawn',
                'last_payout_at'
            ]);
        });
    }
};