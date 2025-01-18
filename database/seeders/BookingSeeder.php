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
        // Generate dummy data for 10 bookings
        for ($i = 1; $i <= 10; $i++) {
            DB::table('bookings')->insert([
                'booking_number' => Str::uuid()->toString(), // Generate a unique booking number
                'amount' => rand(100, 1000) + rand(0, 99) / 100, // Random amount between 100.00 and 1000.99
                'property_id' => rand(1, 10), // Assuming you have properties with IDs 1 to 10
                'user_id' => rand(1, 10), // Assuming you have users with IDs 1 to 10
                'start_date' => Carbon::today()->subDays(rand(1, 30)), // Random start date within the last 30 days
                'end_date' => Carbon::today()->addDays(rand(1, 10)), // Random end date within the next 10 days
                'guest_details' => json_encode(['adults' => rand(1, 4), 'children' => rand(0, 3)]), // Random guest details
                'guest_count' => rand(1, 6), // Random guest count between 1 and 6
                'booking_status' => collect(['pending', 'confirmed', 'canceled'])->random(), // Random booking status
                'payment_status' => collect(['pending', 'completed', 'failed'])->random(), // Random payment status
                'payment_method' => collect(['credit_card', 'paypal', 'bank_transfer'])->random(), // Random payment method
                'payment_date' => Carbon::today()->subDays(rand(0, 10))->toDateString(), // Random payment date within the last 10 days
                'cancellation_reason' => rand(0, 1) ? 'Customer request' : null, // Random cancellation reason or null
                'cancellation_date' => rand(0, 1) ? Carbon::today()->subDays(rand(1, 5))->toDateString() : null, // Random cancellation date or null
            ]);
        }
    }
}
