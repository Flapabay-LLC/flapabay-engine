<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Original bookings
        for ($i = 1; $i <= 10; $i++) {
            DB::table('bookings')->insert([
                'booking_number' => 'BK' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'amount' => rand(100, 1000),
                'listing_id' => rand(1, 5),
                'user_id' => rand(1, 10),
                'start_date' => now()->addDays(rand(1, 30)),
                'end_date' => now()->addDays(rand(31, 60)),
                'guest_details' => 'Adult: ' . rand(1, 4) . ', Children: ' . rand(0, 2),
                'guest_count' => rand(1, 6),
                'booking_status' => ['pending', 'confirmed', 'canceled'][rand(0, 2)],
                'payment_status' => ['pending', 'paid', 'failed'][rand(0, 2)],
                'payment_method' => ['credit_card', 'paypal', 'bank_transfer'][rand(0, 2)],
                'payment_date' => now()->subDays(rand(1, 10))->format('Y-m-d'),
                'cancellation_reason' => rand(0, 1) ? 'User requested cancellation' : null,
                'cancellation_date' => rand(0, 1) ? now()->subDays(rand(1, 5)) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Completed bookings for user 7 (eligible for reviews)
        $completedBookings = [
            [
                'booking_number' => 'BK000011',
                'amount' => 250.00,
                'listing_id' => 1,
                'user_id' => 7,
                'start_date' => now()->subDays(15)->format('Y-m-d'),
                'end_date' => now()->subDays(10)->format('Y-m-d'),
                'guest_details' => 'Adult: 2, Children: 0',
                'guest_count' => 2,
                'booking_status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'credit_card',
                'payment_date' => now()->subDays(20)->format('Y-m-d'),
                'cancellation_reason' => null,
                'cancellation_date' => null,
                'booking_type' => 'stay',
                'created_at' => now()->subDays(25),
                'updated_at' => now()->subDays(10),
            ],
            [
                'booking_number' => 'BK000012',
                'amount' => 180.00,
                'listing_id' => 2,
                'user_id' => 7,
                'start_date' => now()->subDays(30)->format('Y-m-d'),
                'end_date' => now()->subDays(27)->format('Y-m-d'),
                'guest_details' => 'Adult: 1, Children: 1',
                'guest_count' => 2,
                'booking_status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'paypal',
                'payment_date' => now()->subDays(35)->format('Y-m-d'),
                'cancellation_reason' => null,
                'cancellation_date' => null,
                'booking_type' => 'experience',
                'created_at' => now()->subDays(40),
                'updated_at' => now()->subDays(27),
            ],
            [
                'booking_number' => 'BK000013',
                'amount' => 420.00,
                'listing_id' => 3,
                'user_id' => 7,
                'start_date' => now()->subDays(45)->format('Y-m-d'),
                'end_date' => now()->subDays(38)->format('Y-m-d'),
                'guest_details' => 'Adult: 3, Children: 1',
                'guest_count' => 4,
                'booking_status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'bank_transfer',
                'payment_date' => now()->subDays(50)->format('Y-m-d'),
                'cancellation_reason' => null,
                'cancellation_date' => null,
                'booking_type' => 'trip',
                'created_at' => now()->subDays(55),
                'updated_at' => now()->subDays(38),
            ]
        ];

        foreach ($completedBookings as $booking) {
            DB::table('bookings')->insert($booking);
        }
    }
}
