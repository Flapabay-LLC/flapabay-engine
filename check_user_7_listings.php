<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "User 7 listings:\n";
$user = \App\Models\User::find(7);
if($user) {
    $listings = $user->listings;
    echo "Total listings: " . $listings->count() . "\n";
    foreach($listings as $listing) {
        echo "Listing ID: {$listing->id}, Title: {$listing->title}\n";
    }
} else {
    echo "User 7 not found\n";
}

echo "\nAll available listings (for creating host bookings):\n";
$allListings = \App\Models\Listing::take(10)->get();
foreach($allListings as $listing) {
    echo "Listing ID: {$listing->id}, Host: {$listing->user_id}, Title: {$listing->title}\n";
}