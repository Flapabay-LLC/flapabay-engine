<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing payments first
        DB::table('payments')->truncate();

        $payments = [
            // User 7's existing bookings - create payments for them
            [
                'booking_id' => 6,
                'payment_method' => 'credit_card',
                'amount' => 250.00,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'booking_id' => 7,
                'payment_method' => 'paypal',
                'amount' => 180.50,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(12),
                'updated_at' => Carbon::now()->subDays(12),
            ],
            [
                'booking_id' => 10,
                'payment_method' => 'stripe',
                'amount' => 320.75,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(8),
                'updated_at' => Carbon::now()->subDays(8),
            ],
            [
                'booking_id' => 11,
                'payment_method' => 'bank_transfer',
                'amount' => 450.00,
                'status' => 'pending',
                'created_at' => Carbon::now()->subDays(5),
                'updated_at' => Carbon::now()->subDays(5),
            ],
            [
                'booking_id' => 12,
                'payment_method' => 'credit_card',
                'amount' => 195.25,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'booking_id' => 13,
                'payment_method' => 'paypal',
                'amount' => 275.80,
                'status' => 'failed',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            // Additional sample payments for other bookings
            [
                'booking_id' => 1,
                'payment_method' => 'credit_card',
                'amount' => 200.50,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(20),
            ],
            [
                'booking_id' => 2,
                'payment_method' => 'paypal',
                'amount' => 350.75,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(18),
                'updated_at' => Carbon::now()->subDays(18),
            ],
            [
                'booking_id' => 3,
                'payment_method' => 'bank_transfer',
                'amount' => 500.00,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(16),
                'updated_at' => Carbon::now()->subDays(16),
            ],
            [
                'booking_id' => 4,
                'payment_method' => 'stripe',
                'amount' => 150.25,
                'status' => 'refunded',
                'created_at' => Carbon::now()->subDays(14),
                'updated_at' => Carbon::now()->subDays(13),
            ],
        ];

        foreach ($payments as $payment) {
            DB::table('payments')->insert($payment);
        }
        
        echo "PaymentSeeder: Created " . count($payments) . " payment records\n";
    }
}
