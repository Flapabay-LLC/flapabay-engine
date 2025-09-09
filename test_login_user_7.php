<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

try {
    // Find user 7
    $user = User::find(7);
    
    if (!$user) {
        echo "User 7 not found.\n";
        exit;
    }
    
    echo "Testing login for User 7: {$user->fname} {$user->lname} ({$user->email})\n";
    
    // Common passwords to try
    $passwordsToTry = ['password', 'password7', '123456', 'admin', 'test'];
    
    foreach ($passwordsToTry as $password) {
        if (Hash::check($password, $user->password)) {
            echo "SUCCESS! Password found: '{$password}'\n";
            
            // Generate JWT token
            $token = JWTAuth::fromUser($user);
            echo "JWT Token: {$token}\n";
            
            // Save token to file for API testing
            file_put_contents('user_7_token.txt', $token);
            echo "Token saved to user_7_token.txt\n";
            exit;
        }
    }
    
    echo "None of the common passwords worked. You may need to reset the password.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}