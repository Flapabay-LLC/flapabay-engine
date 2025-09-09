<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Property;
use App\Models\User;
use App\Models\Listing;

echo "Checking Properties for User ID 7:\n";
echo "================================\n\n";

// Check if user 7 exists
$user = User::find(7);
if (!$user) {
    echo "User with ID 7 not found!\n";
    exit;
}

echo "User Info:\n";
echo "- ID: {$user->id}\n";
echo "- Name: {$user->fname} {$user->lname}\n";
echo "- Email: {$user->email}\n";
echo "- Is Host: " . ($user->is_host ? 'true' : 'false') . "\n\n";

// Check properties in database
echo "Properties in Database (user_id = 7):\n";
$properties = Property::where('user_id', 7)->get();
echo "Total Properties: {$properties->count()}\n\n";

if ($properties->count() > 0) {
    echo "Property Details:\n";
    foreach ($properties as $property) {
        echo "- Property ID: {$property->id}\n";
        echo "  Title: " . ($property->title ?? 'No title') . "\n";
        echo "  Status: " . ($property->status ?? 'No status') . "\n";
        echo "  Created: {$property->created_at}\n";
        
        // Check if property has associated listing
        $listing = $property->listing;
        if ($listing) {
            echo "  Has Listing: Yes (ID: {$listing->id})\n";
            echo "  Listing Status: " . ($listing->status ?? 'No status') . "\n";
        } else {
            echo "  Has Listing: No\n";
        }
        echo "\n";
    }
}

// Also check listings directly
echo "Listings in Database (user_id = 7):\n";
$listings = Listing::where('user_id', 7)->get();
echo "Total Listings: {$listings->count()}\n\n";

if ($listings->count() > 0) {
    echo "Listing Details:\n";
    foreach ($listings as $listing) {
        echo "- Listing ID: {$listing->id}\n";
        echo "  Property ID: " . ($listing->listing_id ?? 'No property') . "\n";
        echo "  Status: " . ($listing->status ?? 'No status') . "\n";
        echo "  Created: {$listing->created_at}\n\n";
    }
}

echo "Check completed!\n";