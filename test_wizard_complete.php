<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

try {
    // Get or create a test user
    $user = User::where('email', 'test@example.com')->first();
    if (!$user) {
        $user = User::create([
            'fname' => 'Test',
            'lname' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
        echo "Created test user: {$user->email}\n";
    } else {
        echo "Using existing user: {$user->email}\n";
    }
    
    // Generate JWT token
    $token = JWTAuth::fromUser($user);
    echo "Generated token: {$token}\n";
    
    // Test the wizard-listings API
    $url = 'http://localhost:8000/api/v1/wizard-listings';
    $data = [
        'title' => 'Test Property via API',
        'listing_type' => 'stay',
        'price_per_night' => 150
    ];
    
    $options = [
        'http' => [
            'header' => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ],
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    
    if ($result === false) {
        echo "Failed to make API request\n";
        print_r($http_response_header);
    } else {
        echo "API Response: {$result}\n";
        $response = json_decode($result, true);
        if (isset($response['data']['draft_id'])) {
            echo "Successfully created listing with draft_id: {$response['data']['draft_id']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}