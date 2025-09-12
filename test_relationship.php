<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\listing;
use App\Models\User;

echo "Testing listing-User Relationship:\n";
echo "================================\n\n";

// Test 1: Check if listing model has user relationship
echo "1. Testing listing->user relationship:\n";
$listing = listing::first();
if ($listing) {
    echo "   listing ID: {$listing->id}\n";
    echo "   listing user_id: " . ($listing->user_id ?? 'NULL') . "\n";
    
    if ($listing->user_id) {
        $user = $listing->user;
        if ($user) {
            echo "   Associated User: {$user->fname} {$user->lname} (ID: {$user->id})\n";
            echo "   User is_host: " . ($user->is_host ? 'true' : 'false') . "\n";
        } else {
            echo "   No user found for this listing\n";
        }
    } else {
        echo "   listing has no user_id set\n";
    }
} else {
    echo "   No listings found\n";
}

echo "\n";

// Test 2: Check if User model has listings relationship
echo "2. Testing User->listings relationship:\n";
$user = User::where('is_host', true)->first();
if ($user) {
    echo "   User: {$user->fname} {$user->lname} (ID: {$user->id})\n";
    echo "   User is_host: " . ($user->is_host ? 'true' : 'false') . "\n";
    
    $listings = $user->listings;
    echo "   Number of listings owned: {$listings->count()}\n";
    
    if ($listings->count() > 0) {
        echo "   listings owned:\n";
        foreach ($listings->take(3) as $prop) {
            echo "     - listing ID: {$prop->id}, Title: " . ($prop->title ?? 'No title') . "\n";
        }
    }
} else {
    echo "   No host users found\n";
}

echo "\n";
echo "Relationship test completed!\n";