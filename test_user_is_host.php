<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Checking listings table columns:\n";
    $columns = Schema::getColumnListing('listings');
    $userColumns = array_filter($columns, function($col) {
        return strpos($col, 'user') !== false || strpos($col, 'host') !== false;
    });
    echo "User/Host related columns: " . implode(', ', $userColumns) . "\n\n";
    
    echo "Checking listings and their user_ids:\n";
    $listings = App\Models\listing::take(5)->get();
    
    foreach ($listings as $listing) {
        echo "listing ID: {$listing->id}, user_id: " . ($listing->user_id ?? 'NULL') . "\n";
    }
    
    echo "\nTesting listing ID 2 specifically:\n";
    $listing = App\Models\listing::find(2);
    if ($listing) {
        echo "listing ID: {$listing->id}\n";
        echo "listing user_id: " . ($listing->user_id ?? 'NULL') . "\n";
        
        if ($listing->user_id) {
            $user = $listing->user;
            if ($user) {
                echo "User ID: {$user->id}\n";
                echo "User attributes: " . json_encode($user->attributes) . "\n";
                echo "User is_host attribute: " . ($user->is_host ? 'true' : 'false') . "\n";
            } else {
                echo "No user found for this listing\n";
            }
        } else {
            echo "listing has no user_id set\n";
        }
    } else {
        echo "listing not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}