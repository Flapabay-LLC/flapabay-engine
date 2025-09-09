<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

try {
    // Find user with ID 7
    $user = User::find(7);
    
    if ($user) {
        echo "User 7 found:\n";
        echo "ID: {$user->id}\n";
        echo "Name: {$user->fname} {$user->lname}\n";
        echo "Email: {$user->email}\n";
        echo "Phone: {$user->phone}\n";
        echo "Created: {$user->created_at}\n";
        
        // Note: We cannot display the actual password as it's hashed
        echo "Password: [HASHED - Cannot display]\n";
        echo "\nTo test login, you'll need to know the original password or reset it.\n";
        
    } else {
        echo "User with ID 7 not found.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}