<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\listing;
use App\Models\Listing;

try {
    // Get first user
    $user = User::first();
    if (!$user) {
        echo "No users found in database\n";
        exit(1);
    }
    
    echo "Found user: {$user->email} (ID: {$user->id})\n";
    
    // Create listing
    $listing = new listing([
        'status' => 'draft',
        'user_id' => $user->id,
        'is_host' => true
    ]);
    $listing->save();
    
    echo "Created listing with ID: {$listing->id}\n";
    
    // Create listing
    $listing = Listing::create([
        'listing_id' => $listing->id,
        'user_id' => $user->id,
        'listing_type' => 'stay',
        'status' => 'draft',
        'is_completed' => false
    ]);
    
    echo "Successfully created listing with ID: {$listing->id}\n";
    echo "Host ID: {$listing->user_id}\n";
    echo "listing ID: {$listing->listing_id}\n";
    echo "Status: {$listing->status}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}