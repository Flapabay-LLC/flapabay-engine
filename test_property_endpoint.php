<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Testing property endpoint for ID 2...\n";

try {
    // First, let's test the Property model directly
    echo "Testing Property model directly...\n";
    $property = \App\Models\Property::find(2);
    if ($property) {
        echo "Property found: ID " . $property->id . "\n";
        echo "Property user_id: " . $property->user_id . "\n";
        
        // Test toArray() method
        echo "Testing toArray() method...\n";
        $propertyArray = $property->toArray();
        echo "Property toArray() successful\n";
        
        // Check if 'is_host' exists in the array
        if (array_key_exists('is_host', $propertyArray)) {
            echo "WARNING: 'is_host' key found in property array: " . json_encode($propertyArray['is_host']) . "\n";
        } else {
            echo "Good: 'is_host' key not found in property array\n";
        }
    } else {
        echo "Property with ID 2 not found\n";
    }
    
    echo "\nTesting property endpoint...\n";
    // Create a request to the property endpoint
    $request = Illuminate\Http\Request::create('/api/v1/properties/2', 'GET');
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