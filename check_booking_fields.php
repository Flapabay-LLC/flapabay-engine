<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Existing booking field values:\n";
$bookings = \App\Models\Booking::take(5)->get(['id', 'booking_type', 'booking_status', 'payment_status']);
foreach($bookings as $booking) {
    echo "ID: {$booking->id}, Type: " . ($booking->booking_type ?? 'NULL') . ", Status: " . ($booking->booking_status ?? 'NULL') . ", Payment: " . ($booking->payment_status ?? 'NULL') . "\n";
}