<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\listing;
use App\Models\Listing;

try {
    $userId = 7;
    
    echo "=== DETAILED ANALYSIS FOR USER 7 ===\n\n";
    
    // Check user exists
    $user = User::find($userId);
    if (!$user) {
        echo "User $userId not found.\n";
        exit;
    }
    
    echo "User: {$user->fname} {$user->lname} (ID: {$user->id})\n";
    echo "Email: {$user->email}\n\n";
    
    // Check listings
    echo "--- listings TABLE ---\n";
    $listings = listing::where('user_id', $userId)->get();
    echo "Total listings: " . $listings->count() . "\n";
    
    foreach ($listings as $listing) {
        echo "listing ID: {$listing->id}, Status: {$listing->status}, Title: {$listing->title}\n";
    }
    
    // Check listings
    echo "\n--- LISTINGS TABLE ---\n";
    $listings = Listing::where('user_id', $userId)->get();
    echo "Total listings: " . $listings->count() . "\n";
    
    foreach ($listings as $listing) {
        echo "Listing ID: {$listing->id}, Status: {$listing->status}, Title: {$listing->title}, Type: {$listing->listing_type}\n";
    }
    
    // Check draft listings specifically
    echo "\n--- DRAFT listings ---\n";
    $draftlistings = listing::where('user_id', $userId)
        ->where('status', listing::STATUS_DRAFT)
        ->get();
    echo "Draft listings: " . $draftlistings->count() . "\n";
    
    foreach ($draftlistings as $draft) {
        echo "Draft listing ID: {$draft->id}, Title: {$draft->title}\n";
    }
    
    // Total count (what API returns)
    $totalCount = $listings->count() + $draftlistings->count();
    echo "\n--- SUMMARY ---\n";
    echo "Published listings: {$listings->count()}\n";
    echo "Draft listings: {$draftlistings->count()}\n";
    echo "Total (API result): {$totalCount}\n";
    echo "\nThis explains why the API shows {$totalCount} items while our listing query showed only {$listings->count()}.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}