<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Property;
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
    
    // Check properties
    echo "--- PROPERTIES TABLE ---\n";
    $properties = Property::where('user_id', $userId)->get();
    echo "Total properties: " . $properties->count() . "\n";
    
    foreach ($properties as $property) {
        echo "Property ID: {$property->id}, Status: {$property->status}, Title: {$property->title}\n";
    }
    
    // Check listings
    echo "\n--- LISTINGS TABLE ---\n";
    $listings = Listing::where('user_id', $userId)->get();
    echo "Total listings: " . $listings->count() . "\n";
    
    foreach ($listings as $listing) {
        echo "Listing ID: {$listing->id}, Status: {$listing->status}, Title: {$listing->title}, Type: {$listing->listing_type}\n";
    }
    
    // Check draft properties specifically
    echo "\n--- DRAFT PROPERTIES ---\n";
    $draftProperties = Property::where('user_id', $userId)
        ->where('status', Property::STATUS_DRAFT)
        ->get();
    echo "Draft properties: " . $draftProperties->count() . "\n";
    
    foreach ($draftProperties as $draft) {
        echo "Draft Property ID: {$draft->id}, Title: {$draft->title}\n";
    }
    
    // Total count (what API returns)
    $totalCount = $listings->count() + $draftProperties->count();
    echo "\n--- SUMMARY ---\n";
    echo "Published listings: {$listings->count()}\n";
    echo "Draft properties: {$draftProperties->count()}\n";
    echo "Total (API result): {$totalCount}\n";
    echo "\nThis explains why the API shows {$totalCount} items while our property query showed only {$properties->count()}.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}