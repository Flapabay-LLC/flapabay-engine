<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Testing listing endpoint for ID 2...\n";

try {
    // First, let's test the listing model directly
    echo "Testing listing model directly...\n";
    $listing = \App\Models\listing::find(2);
    if ($listing) {
        echo "listing found: ID " . $listing->id . "\n";
        echo "listing user_id: " . $listing->user_id . "\n";
        
        // Test toArray() method
        echo "Testing toArray() method...\n";
        $listingArray = $listing->toArray();
        echo "listing toArray() successful\n";
        
        // Check if 'is_host' exists in the array
        if (array_key_exists('is_host', $listingArray)) {
            echo "WARNING: 'is_host' key found in listing array: " . json_encode($listingArray['is_host']) . "\n";
        } else {
            echo "Good: 'is_host' key not found in listing array\n";
        }
    } else {
        echo "listing with ID 2 not found\n";
    }
    
    echo "\nTesting listing endpoint...\n";
    // Create a request to the listing endpoint
    $request = Illuminate\Http\Request::create('/api/v1/listings/2', 'GET');
    $request->headers->set('Accept', 'application/json');
    
    // Process the request
    $response = $kernel->handle($request);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Content: " . $response->getContent() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";