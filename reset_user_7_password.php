<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "\n=== Resetting User 7 Password ===\n";

// Update user 7's password to a known value
$updated = DB::table('users')
    ->where('email', 'georgemunganga@gmail.com')
    ->update([
        'password' => Hash::make('password123'),
        'updated_at' => now()
    ]);

if ($updated) {
    echo "Successfully updated password for georgemunganga@gmail.com\n";
    echo "New password: password123\n";
} else {
    echo "Failed to update password - user not found\n";
}

echo "=== Password Reset Complete ===\n";