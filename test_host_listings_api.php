<?php

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

// Generate token for user 7
$user = User::find(7);
if (!$user) {
    echo "User 7 not found\n";
    exit(1);
}

$token = JWTAuth::fromUser($user);
echo "Generated token for user {$user->id}: {$token}\n\n";

// Test the API endpoint
$url = 'http://localhost:8000/api/v1/listings/host';
$headers = [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
];

// Test with listing_type parameter
$urlWithParams = $url . '?listing_type=stay';

echo "Testing GET {$urlWithParams}\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $urlWithParams);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    echo "cURL Error: {$error}\n";
} else {
    echo "HTTP Status: {$httpCode}\n";
    echo "Response: {$response}\n";
}