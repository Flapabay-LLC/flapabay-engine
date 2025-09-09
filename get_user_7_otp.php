<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

try {
    // Find user with ID 7
    $user = User::find(7);
    
    if ($user) {
        echo "User 7 found:\n";
        echo "ID: {$user->id}\n";
        echo "Name: {$user->fname} {$user->lname}\n";
        echo "Email: {$user->email}\n";
        echo "Phone: {$user->phone}\n";
        echo "OTP: {$user->otp}\n";
        echo "OTP Expires At: {$user->otp_expires_at}\n";
        echo "OTP Verified At: {$user->otp_verified_at}\n";
        
        // If OTP exists, generate token using OTP login simulation
        if ($user->otp) {
            echo "\n--- Testing OTP Login ---\n";
            $token = JWTAuth::fromUser($user);
            echo "Generated JWT Token: {$token}\n";
            
            // Save token to file for API testing
            file_put_contents('user_7_token.txt', $token);
            echo "Token saved to user_7_token.txt\n";
        } else {
            echo "\nNo OTP found for user 7. Need to generate OTP first.\n";
        }
        
    } else {
        echo "User with ID 7 not found.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}