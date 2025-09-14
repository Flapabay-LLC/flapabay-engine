<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Withdrawal;

class User7DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Creating additional bookings for User 7's listings (as host)...\n";
        
        // Create bookings for User 7's listings (User 7 as host)
        $hostBookings = [
            [
                'user_id' => 3, // Guest user ID
                'listing_id' => 11, // User 7's listing
                'start_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(27)->format('Y-m-d'),
                'guest_count' => 2,
                'amount' => 450.00,
                'booking_status' => 'confirmed',
                'booking_type' => 'normal',
                'payment_status' => 'paid',
                'created_at' => Carbon::now()->subDays(35),
                'updated_at' => Carbon::now()->subDays(30),
            ],
            [
                'user_id' => 1, // Guest user ID
                'listing_id' => 12, // User 7's listing
                'start_date' => Carbon::now()->subDays(25)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(22)->format('Y-m-d'),
                'guest_count' => 4,
                'amount' => 680.00,
                'booking_status' => 'confirmed',
                'booking_type' => 'normal',
                'payment_status' => 'paid',
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(25),
            ],
            [
                'user_id' => 6, // Guest user ID
                'listing_id' => 15, // User 7's listing - Victoria Falls Adventure Tour
                'start_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(18)->format('Y-m-d'),
                'guest_count' => 3,
                'amount' => 890.00,
                'booking_status' => 'confirmed',
                'booking_type' => 'normal',
                'payment_status' => 'paid',
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now()->subDays(20),
            ],
            [
                'user_id' => 3, // Guest user ID
                'listing_id' => 16, // User 7's listing - Lusaka Cultural Walking Tour
                'start_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(14)->format('Y-m-d'),
                'guest_count' => 2,
                'amount' => 120.00,
                'booking_status' => 'confirmed',
                'booking_type' => 'normal',
                'payment_status' => 'paid',
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'user_id' => 1, // Guest user ID
                'listing_id' => 17, // User 7's listing - South Luangwa Safari Experience
                'start_date' => Carbon::now()->subDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(7)->format('Y-m-d'),
                'guest_count' => 2,
                'amount' => 1250.00,
                'booking_status' => 'confirmed',
                'booking_type' => 'normal',
                'payment_status' => 'paid',
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(10),
            ],
        ];

        $createdBookingIds = [];
        foreach ($hostBookings as $booking) {
            $bookingId = DB::table('bookings')->insertGetId($booking);
            $createdBookingIds[] = $bookingId;
        }
        
        echo "Created " . count($createdBookingIds) . " host bookings for User 7\n";

        // Create payments for these host bookings
        echo "Creating payments for host bookings...\n";
        $hostPayments = [
            [
                'booking_id' => $createdBookingIds[0],
                'payment_method' => 'credit_card',
                'amount' => 450.00,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(30),
                'updated_at' => Carbon::now()->subDays(30),
            ],
            [
                'booking_id' => $createdBookingIds[1],
                'payment_method' => 'paypal',
                'amount' => 680.00,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(25),
                'updated_at' => Carbon::now()->subDays(25),
            ],
            [
                'booking_id' => $createdBookingIds[2],
                'payment_method' => 'stripe',
                'amount' => 890.00,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(20),
            ],
            [
                'booking_id' => $createdBookingIds[3],
                'payment_method' => 'credit_card',
                'amount' => 120.00,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(15),
            ],
            [
                'booking_id' => $createdBookingIds[4],
                'payment_method' => 'bank_transfer',
                'amount' => 1250.00,
                'status' => 'completed',
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],
        ];

        foreach ($hostPayments as $payment) {
            DB::table('payments')->insert($payment);
        }
        
        echo "Created " . count($hostPayments) . " payments for host bookings\n";

        // Create withdrawal records for User 7 (as host receiving payouts)
        echo "Creating withdrawal records for User 7...\n";
        $withdrawals = [
            [
                'user_id' => 7,
                'payment_method_id' => 1,
                'amount' => 400.00, // Host earnings from booking (minus platform fee)
                'currency' => 'USD',
                'status' => 'completed',
                'reference_id' => 'WD_' . uniqid(),
                'notes' => 'Host earnings withdrawal',
                'metadata' => json_encode([
                    'bank_name' => 'Standard Bank Zambia',
                    'account_number' => '****1234',
                    'account_holder' => 'User Seven'
                ]),
                'requested_at' => Carbon::now()->subDays(28),
                'processed_at' => Carbon::now()->subDays(25),
                'completed_at' => Carbon::now()->subDays(25),
                'created_at' => Carbon::now()->subDays(28),
                'updated_at' => Carbon::now()->subDays(25),
            ],
            [
                'user_id' => 7,
                'payment_method_id' => 2,
                'amount' => 600.00,
                'currency' => 'USD',
                'status' => 'completed',
                'reference_id' => 'WD_' . uniqid(),
                'notes' => 'Host earnings withdrawal',
                'metadata' => json_encode([
                    'paypal_email' => 'user7@example.com'
                ]),
                'requested_at' => Carbon::now()->subDays(20),
                'processed_at' => Carbon::now()->subDays(18),
                'completed_at' => Carbon::now()->subDays(18),
                'created_at' => Carbon::now()->subDays(20),
                'updated_at' => Carbon::now()->subDays(18),
            ],
            [
                'user_id' => 7,
                'payment_method_id' => 3,
                'amount' => 780.00,
                'currency' => 'USD',
                'status' => 'completed',
                'reference_id' => 'WD_' . uniqid(),
                'notes' => 'Host earnings withdrawal',
                'metadata' => json_encode([
                    'provider' => 'MTN Mobile Money',
                    'phone_number' => '+260971234567'
                ]),
                'requested_at' => Carbon::now()->subDays(15),
                'processed_at' => Carbon::now()->subDays(12),
                'completed_at' => Carbon::now()->subDays(12),
                'created_at' => Carbon::now()->subDays(15),
                'updated_at' => Carbon::now()->subDays(12),
            ],
            [
                'user_id' => 7,
                'payment_method_id' => 1,
                'amount' => 100.00,
                'currency' => 'USD',
                'status' => 'pending',
                'reference_id' => 'WD_' . uniqid(),
                'notes' => 'Host earnings withdrawal - pending',
                'metadata' => json_encode([
                    'bank_name' => 'Standard Bank Zambia',
                    'account_number' => '****1234',
                    'account_holder' => 'User Seven'
                ]),
                'requested_at' => Carbon::now()->subDays(8),
                'processed_at' => null,
                'completed_at' => null,
                'created_at' => Carbon::now()->subDays(8),
                'updated_at' => Carbon::now()->subDays(8),
            ],
            [
                'user_id' => 7,
                'payment_method_id' => 1,
                'amount' => 1100.00,
                'currency' => 'USD',
                'status' => 'processing',
                'reference_id' => 'WD_' . uniqid(),
                'notes' => 'Host earnings withdrawal - processing',
                'metadata' => json_encode([
                    'bank_name' => 'Standard Bank Zambia',
                    'account_number' => '****1234',
                    'account_holder' => 'User Seven'
                ]),
                'requested_at' => Carbon::now()->subDays(3),
                'processed_at' => Carbon::now()->subDays(2),
                'completed_at' => null,
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($withdrawals as $withdrawal) {
            DB::table('withdrawals')->insert($withdrawal);
        }
        
        echo "Created " . count($withdrawals) . " withdrawal records for User 7\n";
        echo "User7DataSeeder completed successfully!\n";
    }
}