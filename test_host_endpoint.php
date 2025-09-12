<?php

// Read the JWT token from file
$token = trim(file_get_contents('user_7_token.txt'));

echo "Testing /api/v1/listings/host endpoint for user 7\n";
echo "Using token: " . substr($token, 0, 50) . "...\n\n";

// API endpoint
$url = 'http://localhost:3000/api/v1/listings/host';

// Headers with JWT token
$headers = [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json',
    'Accept: application/json'
];

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Check for cURL errors
if ($error) {
    echo "cURL Error: $error\n";
    exit(1);
}

echo "HTTP Status Code: $httpCode\n";
echo "Response:\n";

// Pretty print JSON response
if ($response) {
    $decodedResponse = json_decode($response, true);
    if ($decodedResponse) {
        echo json_encode($decodedResponse, JSON_PRETTY_PRINT) . "\n";
        
        // Count listings if data exists
        if (isset($decodedResponse['data']) && is_array($decodedResponse['data'])) {
            $listingCount = count($decodedResponse['data']);
            echo "\n--- ANALYSIS ---\n";
            echo "API returned $listingCount listings for user 7\n";
            echo "Database showed 1 listing for user 7\n";
            
            if ($listingCount != 1) {
                echo "DISCREPANCY FOUND: API shows $listingCount listings, but database has 1\n";
            } else {
                echo "MATCH: Both API and database show the same count\n";
            }
        }
    } else {
        echo "Raw response: $response\n";
    }
} else {
    echo "No response received\n";
}