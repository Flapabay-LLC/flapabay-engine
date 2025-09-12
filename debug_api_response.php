<?php

// Read the JWT token from file
$token = trim(file_get_contents('user_7_token.txt'));

echo "=== DEBUGGING API RESPONSE ===\n";
echo "Testing /api/v1/listings/host endpoint for user 7\n\n";

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

echo "HTTP Status Code: $httpCode\n\n";

// Decode and analyze response
if ($response) {
    $decodedResponse = json_decode($response, true);
    if ($decodedResponse) {
        echo "=== RESPONSE STRUCTURE ===\n";
        echo "Code: " . ($decodedResponse['code'] ?? 'N/A') . "\n";
        echo "Message: " . ($decodedResponse['message'] ?? 'N/A') . "\n\n";
        
        if (isset($decodedResponse['data'])) {
            $data = $decodedResponse['data'];
            echo "=== PAGINATION INFO ===\n";
            echo "Current Page: " . ($data['current_page'] ?? 'N/A') . "\n";
            echo "Total Items: " . ($data['total'] ?? 'N/A') . "\n";
            echo "Per Page: " . ($data['per_page'] ?? 'N/A') . "\n";
            echo "From: " . ($data['from'] ?? 'N/A') . "\n";
            echo "To: " . ($data['to'] ?? 'N/A') . "\n\n";
            
            if (isset($data['data']) && is_array($data['data'])) {
                $items = $data['data'];
                echo "=== ITEMS ANALYSIS ===\n";
                echo "Number of items in data array: " . count($items) . "\n\n";
                
                foreach ($items as $index => $item) {
                    echo "Item " . ($index + 1) . ":\n";
                    echo "  ID: " . ($item['id'] ?? 'N/A') . "\n";
                    echo "  User ID: " . ($item['user_id'] ?? 'N/A') . "\n";
                    echo "  Status: " . ($item['status'] ?? 'N/A') . "\n";
                    echo "  Title: '" . ($item['title'] ?? 'N/A') . "'\n";
                    echo "  Type: " . ($item['listing_type'] ?? 'N/A') . "\n";
                    echo "  Created: " . ($item['created_at'] ?? 'N/A') . "\n";
                    echo "\n";
                }
                
                echo "=== SUMMARY ===\n";
                echo "API pagination says total: " . ($data['total'] ?? 'N/A') . "\n";
                echo "Actual items in response: " . count($items) . "\n";
                echo "Database analysis showed: 2 items (1 listing + 1 draft listing)\n";
            }
        }
    } else {
        echo "Failed to decode JSON response\n";
        echo "Raw response: $response\n";
    }
} else {
    echo "No response received\n";
}