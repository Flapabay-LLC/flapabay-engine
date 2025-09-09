<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Checking properties table columns:\n";
    $columns = Schema::getColumnListing('properties');
    $userColumns = array_filter($columns, function($col) {
        return strpos($col, 'user') !== false || strpos($col, 'host') !== false;
    });
    echo "User/Host related columns: " . implode(', ', $userColumns) . "\n\n";
    
    echo "Checking properties and their user_ids:\n";
    $properties = App\Models\Property::take(5)->get();
    
    foreach ($properties as $property) {
        echo "Property ID: {$property->id}, user_id: " . ($property->user_id ?? 'NULL') . "\n";
    }
    
    echo "\nTesting property ID 2 specifically:\n";
    $property = App\Models\Property::find(2);
    if ($property) {
        echo "Property ID: {$property->id}\n";
        echo "Property user_id: " . ($property->user_id ?? 'NULL') . "\n";
        
        if ($property->user_id) {
            $user = $property->user;
            if ($user) {
                echo "User ID: {$user->id}\n";
                echo "User attributes: " . json_encode($user->attributes) . "\n";
                echo "User is_host attribute: " . ($user->is_host ? 'true' : 'false') . "\n";
            } else {
                echo "No user found for this property\n";
            }
        } else {
            echo "Property has no user_id set\n";
        }
    } else {
        echo "Property not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}