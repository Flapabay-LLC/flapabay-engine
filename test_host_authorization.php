<?php

echo "\n=== Testing Host Authorization for Experience CRUD ===\n\n";

// Test data for API calls
$baseUrl = 'http://localhost:8000/api';

// Test 1: Test without authentication
echo "Test 1: Unauthenticated request\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/experiences');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "GET /experiences - Status: $httpCode\n";
echo "Response: " . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Test with invalid token
echo "Test 2: Invalid token\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $baseUrl . '/experiences');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer invalid_token_here',
    'Content-Type: application/json',
    'Accept: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "GET /experiences - Status: $httpCode\n";
echo "Response: " . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n\n";

echo "\n=== Manual Testing Instructions ===\n";
echo "To test host authorization properly, you need to:\n";
echo "1. Create a user with is_host = false in the database\n";
echo "2. Create a user with is_host = true in the database\n";
echo "3. Generate JWT tokens for both users\n";
echo "4. Test API endpoints with both tokens\n\n";

echo "Expected behavior:\n";
echo "- Non-host users should get 403 'Only hosts can manage experiences'\n";
echo "- Host users should be able to access their own experiences\n";
echo "- Users can only see/modify experiences they own (user_id match)\n\n";

echo "=== Host Authorization Tests Complete ===\n";