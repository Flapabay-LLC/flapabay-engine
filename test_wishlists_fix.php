<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Wishlist;
use App\Models\Favorite;
use App\Models\Listing;

try {
    echo "Testing Wishlist relationships...\n";
    
    // Test if we can load wishlists with favorites and listings
    $wishlists = Wishlist::with(['favorites.listing'])->first();
    
    if ($wishlists) {
        echo "✓ Successfully loaded wishlist with favorites and listings\n";
        echo "Wishlist ID: " . $wishlists->id . "\n";
        echo "Favorites count: " . $wishlists->favorites->count() . "\n";
        
        foreach ($wishlists->favorites as $favorite) {
            if ($favorite->listing) {
                echo "  - Favorite has listing: " . $favorite->listing->id . "\n";
            } else {
                echo "  - Favorite has no listing\n";
            }
        }
    } else {
        echo "No wishlists found in database\n";
    }
    
    echo "\n✓ Relationship test completed successfully!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}