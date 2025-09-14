<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Creating additional payment records to simulate transaction history...\n";
        
        // Get existing bookings to create additional payment attempts for
        $bookings = DB::table('bookings')->where('user_id', 7)->get();
        
        $additionalPayments = [];
        
        foreach ($bookings as $booking) {
            // Create a failed payment attempt (simulating declined card)
            $additionalPayments[] = [
                'booking_id' => $booking->id,
                'payment_method' => 'credit_card',
                'amount' => $booking->amount,
                'status' => 'failed',
                'created_at' => Carbon::parse($booking->created_at)->subMinutes(10),
                'updated_at' => Carbon::parse($booking->created_at)->subMinutes(8),
            ];
            
            // Create a pending payment attempt (simulating processing delay)
            $additionalPayments[] = [
                'booking_id' => $booking->id,
                'payment_method' => 'paypal',
                'amount' => $booking->amount,
                'status' => 'pending',
                'created_at' => Carbon::parse($booking->created_at)->subMinutes(5),
                'updated_at' => Carbon::parse($booking->created_at)->subMinutes(3),
            ];
        }
        
        // Create some additional payment records for other users' bookings
        $otherBookings = DB::table('bookings')->where('user_id', '!=', 7)->limit(3)->get();
        
        foreach ($otherBookings as $booking) {
            $additionalPayments[] = [
                'booking_id' => $booking->id,
                'payment_method' => 'stripe',
                'amount' => $booking->amount,
                'status' => 'completed',
                'created_at' => Carbon::parse($booking->created_at)->addMinutes(2),
                'updated_at' => Carbon::parse($booking->created_at)->addMinutes(5),
            ];
            
            $additionalPayments[] = [
                'booking_id' => $booking->id,
                'payment_method' => 'apple_pay',
                'amount' => $booking->amount * 0.5, // Partial payment
                'status' => 'failed',
                'created_at' => Carbon::parse($booking->created_at)->subHours(1),
                'updated_at' => Carbon::parse($booking->created_at)->subMinutes(55),
            ];
        }
        
        // Insert additional payment records
        if (!empty($additionalPayments)) {
            DB::table('payments')->insert($additionalPayments);
        }
        
        echo "Created " . count($additionalPayments) . " additional payment records\n";
        echo "PaymentHistorySeeder completed successfully!\n";
    }
}