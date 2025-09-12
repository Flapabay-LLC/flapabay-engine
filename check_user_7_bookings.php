<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Booking;
use Carbon\Carbon;

// Find user 7
$user = User::find(7);

if (!$user) {
    echo "User 7 not found\n";
    exit(1);
}

echo "User 7: {$user->email}\n";
echo "===================\n";

// Get all bookings for user 7
$bookings = Booking::where('user_id', 7)
    ->with(['listing:id,title', 'userReviews'])
    ->get();

echo "Total bookings: " . $bookings->count() . "\n\n";

foreach ($bookings as $booking) {
    echo "Booking ID: {$booking->id}\n";
    echo "Listing: " . ($booking->listing->title ?? 'N/A') . "\n";
    echo "Status: {$booking->booking_status}\n";
    echo "Dates: {$booking->start_date} to {$booking->end_date}\n";
    echo "Has Review: " . ($booking->userReviews->count() > 0 ? 'Yes' : 'No') . "\n";
    
    if ($booking->booking_status === 'completed') {
        $endDate = Carbon::parse($booking->end_date);
        $daysSinceEnd = $endDate->diffInDays(Carbon::now());
        $canReview = $daysSinceEnd <= 30 && $booking->userReviews->count() === 0;
        echo "Days since completion: {$daysSinceEnd}\n";
        echo "Eligible for review: " . ($canReview ? 'Yes' : 'No') . "\n";
    }
    
    echo "---\n";
}

// Check specifically for completed bookings without reviews
$eligibleBookings = Booking::where('user_id', 7)
    ->where('booking_status', 'completed')
    ->whereDoesntHave('userReviews')
    ->where('end_date', '>=', Carbon::now()->subDays(30))
    ->count();

echo "\nBookings eligible for review: {$eligibleBookings}\n";