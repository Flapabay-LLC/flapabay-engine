<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Find user 7
$user = App\Models\User::find(7);

if (!$user) {
    echo "User 7 not found\n";
    exit(1);
}

// Generate JWT token
$token = auth('api')->login($user);

echo "Generated token for user {$user->id}: {$user->email}\n";
echo $token . "\n";

// Save to file
file_put_contents('user_7_token.txt', $token);
echo "Token saved to user_7_token.txt\n";