<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing Payment APIs with Seeded Data ===\n\n";

// Test 1: Get all payments
echo "1. Testing GET /api/payments\n";
$payments = DB::table('payments')->get();
echo "Total payments in database: " . $payments->count() . "\n";
echo "Sample payment: ID {$payments->first()->id}, Amount: {$payments->first()->amount}, Status: {$payments->first()->status}\n\n";

// Test 2: Get payments for user 7's bookings (as guest)
echo "2. Testing payments for User 7's guest bookings\n";
$user7GuestPayments = DB::table('payments')
    ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
    ->where('bookings.user_id', 7)
    ->select('payments.*', 'bookings.user_id as guest_id')
    ->get();
echo "User 7's guest payments: " . $user7GuestPayments->count() . "\n";
foreach ($user7GuestPayments->take(3) as $payment) {
    echo "  - Payment ID: {$payment->id}, Amount: {$payment->amount}, Status: {$payment->status}\n";
}
echo "\n";

// Test 3: Get payments for user 7's listings (as host)
echo "3. Testing payments for User 7's host bookings\n";
$user7HostPayments = DB::table('payments')
    ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
    ->join('listings', 'bookings.listing_id', '=', 'listings.id')
    ->where('listings.user_id', 7)
    ->select('payments.*', 'bookings.user_id as guest_id', 'listings.user_id as host_id')
    ->get();
echo "User 7's host payments: " . $user7HostPayments->count() . "\n";
foreach ($user7HostPayments->take(3) as $payment) {
    echo "  - Payment ID: {$payment->id}, Amount: {$payment->amount}, Status: {$payment->status}, Guest: {$payment->guest_id}\n";
}
echo "\n";

// Test 4: Get withdrawals for user 7
echo "4. Testing withdrawals for User 7\n";
$user7Withdrawals = DB::table('withdrawals')->where('user_id', 7)->get();
echo "User 7's withdrawals: " . $user7Withdrawals->count() . "\n";
foreach ($user7Withdrawals->take(3) as $withdrawal) {
    echo "  - Withdrawal ID: {$withdrawal->id}, Amount: {$withdrawal->amount}, Status: {$withdrawal->status}\n";
}
echo "\n";

// Test 5: Payment status breakdown
echo "5. Payment status breakdown\n";
$statusCounts = DB::table('payments')
    ->select('status', DB::raw('count(*) as count'))
    ->groupBy('status')
    ->get();
foreach ($statusCounts as $status) {
    echo "  - {$status->status}: {$status->count} payments\n";
}
echo "\n";

// Test 6: Payment method breakdown
echo "6. Payment method breakdown\n";
$methodCounts = DB::table('payments')
    ->select('payment_method', DB::raw('count(*) as count'))
    ->groupBy('payment_method')
    ->get();
foreach ($methodCounts as $method) {
    echo "  - {$method->payment_method}: {$method->count} payments\n";
}
echo "\n";

// Test 7: Total earnings for user 7 as host
echo "7. User 7's host earnings calculation\n";
$hostEarnings = DB::table('payments')
    ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
    ->join('listings', 'bookings.listing_id', '=', 'listings.id')
    ->where('listings.user_id', 7)
    ->where('payments.status', 'completed')
    ->sum('payments.amount');
echo "Total completed payments for User 7's listings: $" . number_format($hostEarnings, 2) . "\n";

$totalWithdrawals = DB::table('withdrawals')
    ->where('user_id', 7)
    ->where('status', 'completed')
    ->sum('amount');
echo "Total completed withdrawals: $" . number_format($totalWithdrawals, 2) . "\n";
echo "Remaining balance: $" . number_format($hostEarnings - $totalWithdrawals, 2) . "\n\n";

echo "=== Payment API Testing Complete ===\n";