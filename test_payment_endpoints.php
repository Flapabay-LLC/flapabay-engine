<?php

require_once 'vendor/autoload.php';

echo "\n=== Testing Payment API Endpoints ===\n";

// Base URL for the API
$baseUrl = 'http://localhost:8000/api/v1';

// Get a user token for authentication (using user 7)
$loginData = [
    'email' => 'georgemunganga@gmail.com',
    'password' => 'password123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Response (HTTP $httpCode): $response\n\n";

$loginResponse = json_decode($response, true);
$token = $loginResponse['data']['token'] ?? null;

if (!$token) {
    echo "Failed to get authentication token. Exiting.\n";
    echo "Login response structure: " . print_r($loginResponse, true) . "\n";
    exit(1);
}

echo "Authentication successful. Token: " . substr($token, 0, 20) . "...\n\n";



// Test payment endpoints with correct v1 prefix
$endpoints = [
    '/earnings/balance' => 'GET',
    '/earnings/total' => 'GET',
    '/withdrawals' => 'GET',
    '/payout-methods' => 'GET',
    '/payment-history' => 'GET'
];

foreach ($endpoints as $endpoint => $method) {
    echo "Testing $method $endpoint:\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Response (HTTP $httpCode): $response\n\n";
}

// Test creating a payout method
echo "Testing payout method creation:\n";
$payoutMethodData = [
    'type' => 'bank_account',
    'account_number' => '1234567890',
    'routing_number' => '021000021',
    'account_holder_name' => 'User Seven'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/payout-methods');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payoutMethodData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Payout Method Creation Response (HTTP $httpCode): $response\n\n";

// Test creating a withdrawal request
echo "Testing withdrawal request:\n";
$withdrawalData = [
    'payment_method_id' => 1,
    'amount' => 100.00,
    'currency' => 'USD'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/withdrawals/request');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($withdrawalData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Withdrawal Request Response (HTTP $httpCode): $response\n\n";

echo "=== Payment API Testing Complete ===\n";