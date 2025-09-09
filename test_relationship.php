<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Property;
use App\Models\User;

echo "Testing Property-User Relationship:\n";
echo "================================\n\n";

// Test 1: Check if Property model has user relationship
echo "1. Testing Property->user relationship:\n";
$property = Property::first();
if ($property) {
    echo "   Property ID: {$property->id}\n";
    echo "   Property user_id: " . ($property->user_id ?? 'NULL') . "\n";
    
    if ($property->user_id) {
        $user = $property->user;
        if ($user) {
            echo "   Associated User: {$user->fname} {$user->lname} (ID: {$user->id})\n";
            echo "   User is_host: " . ($user->is_host ? 'true' : 'false') . "\n";
        } else {
            echo "   No user found for this property\n";
        }
    } else {
        echo "   Property has no user_id set\n";
    }
} else {
    echo "   No properties found\n";
}

echo "\n";

// Test 2: Check if User model has properties relationship
echo "2. Testing User->properties relationship:\n";
$user = User::where('is_host', true)->first();
if ($user) {
    echo "   User: {$user->fname} {$user->lname} (ID: {$user->id})\n";
    echo "   User is_host: " . ($user->is_host ? 'true' : 'false') . "\n";
    
    $properties = $user->properties;
    echo "   Number of properties owned: {$properties->count()}\n";
    
    if ($properties->count() > 0) {
        echo "   Properties owned:\n";
        foreach ($properties->take(3) as $prop) {
            echo "     - Property ID: {$prop->id}, Title: " . ($prop->title ?? 'No title') . "\n";
        }
    }
} else {
    echo "   No host users found\n";
}

echo "\n";
echo "Relationship test completed!\n";