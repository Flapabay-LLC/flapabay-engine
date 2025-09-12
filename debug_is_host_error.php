<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Debugging is_host error...\n";

try {
    // Test User model directly
    echo "\n1. Testing User model directly...\n";
    $user = \App\Models\User::first();
    if ($user) {
        echo "User found: ID " . $user->id . "\n";
        echo "User attributes: " . json_encode($user->getAttributes()) . "\n";
        
        // Check if is_host key exists in attributes
        $attributes = $user->getAttributes();
        if (array_key_exists('is_host', $attributes)) {
            echo "is_host key EXISTS in attributes: " . json_encode($attributes['is_host']) . "\n";
        } else {
            echo "is_host key MISSING from attributes\n";
        }
        
        // Test the accessor directly
        echo "\n2. Testing is_host accessor...\n";
        try {
            $isHost = $user->is_host;
            echo "is_host accessor works: " . json_encode($isHost) . "\n";
        } catch (Exception $e) {
            echo "is_host accessor ERROR: " . $e->getMessage() . "\n";
        }
        
        // Test toArray() method
        echo "\n3. Testing User toArray() method...\n";
        try {
            $userArray = $user->toArray();
            echo "User toArray() successful\n";
            if (array_key_exists('is_host', $userArray)) {
                echo "is_host in toArray(): " . json_encode($userArray['is_host']) . "\n";
            } else {
                echo "is_host NOT in toArray()\n";
            }
        } catch (Exception $e) {
            echo "User toArray() ERROR: " . $e->getMessage() . "\n";
        }
    } else {
        echo "No users found in database\n";
    }
    
    // Test Listing with User relationship
    echo "\n4. Testing Listing with User relationship...\n";
    $listing = \App\Models\Listing::with('user')->first();
    if ($listing && $listing->user) {
        echo "Listing found with user: Listing ID " . $listing->id . ", User ID " . $listing->user->id . "\n";
        
        try {
            $listingArray = $listing->toArray();
            echo "Listing toArray() successful\n";
            
            if (isset($listingArray['user']['is_host'])) {
                echo "User is_host in listing array: " . json_encode($listingArray['user']['is_host']) . "\n";
            } else {
                echo "User is_host NOT in listing array\n";
            }
        } catch (Exception $e) {
            echo "Listing toArray() ERROR: " . $e->getMessage() . "\n";
            echo "Error file: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
        }
    } else {
        echo "No listing with user found\n";
    }
    
} catch (Exception $e) {
    echo "General ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\nDebug completed.\n";