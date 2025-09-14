<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "User 7 bookings (as guest):\n";
$guestBookings = App\Models\Booking::where('user_id', 7)->get();
foreach($guestBookings as $booking) {
    echo "Booking ID: {$booking->id}, Listing: {$booking->listing_id}, Amount: " . ($booking->total_amount ?? 'N/A') . "\n";
    // Check if payment exists
    $payment = $booking->payment;
    if($payment) {
        echo "  - Payment: {$payment->amount}, Status: {$payment->status}, Method: {$payment->payment_method}\n";
    } else {
        echo "  - No payment record\n";
    }
}

echo "\nUser 7 host bookings (via listings):\n";
$user = App\Models\User::find(7);
if($user) {
    $hostBookings = $user->hostBookings;
    foreach($hostBookings as $booking) {
        echo "Booking ID: {$booking->id}, Guest: {$booking->user_id}, Amount: " . ($booking->total_amount ?? 'N/A') . "\n";
        // Check if payment exists
        $payment = $booking->payment;
        if($payment) {
            echo "  - Payment: {$payment->amount}, Status: {$payment->status}, Method: {$payment->payment_method}\n";
        } else {
            echo "  - No payment record\n";
        }
    }
} else {
    echo "User 7 not found\n";
}

echo "\nExisting withdrawals for user 7:\n";
$withdrawals = App\Models\Withdrawal::where('user_id', 7)->get();
foreach($withdrawals as $withdrawal) {
    echo "Withdrawal ID: {$withdrawal->id}, Amount: {$withdrawal->amount}, Status: {$withdrawal->status}\n";
}