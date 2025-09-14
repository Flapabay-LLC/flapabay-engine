<?php

require_once 'vendor/autoload.php';

// Configuration
$baseUrl = 'http://localhost:8000/api/v1';
$token = trim(file_get_contents('user_7_token.txt')); // Use existing user 7 token

// Helper function to make API requests
function makeRequest($method, $url, $data = null, $token = null) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            $token ? "Authorization: Bearer $token" : ''
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);
    
    if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        throw new Exception("cURL Error: $error");
    }
    
    return [
        'status_code' => $httpCode,
        'body' => json_decode($response, true),
        'raw_body' => $response
    ];
}

echo "=== Testing Experience CRUD API Endpoints ===\n\n";

try {
    // Test 1: GET /experiences (List experiences)
    echo "1. Testing GET /experiences (List experiences)\n";
    $response = makeRequest('GET', "$baseUrl/experiences", null, $token);
    echo "Status Code: {$response['status_code']}\n";
    if ($response['status_code'] === 200) {
        echo "✅ Successfully fetched experiences list\n";
        $experienceCount = isset($response['body']['data']['data']) ? count($response['body']['data']['data']) : 0;
        echo "Found $experienceCount experiences\n";
    } else {
        echo "❌ Failed to fetch experiences\n";
        echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";

    // Test 2: POST /experiences (Create new experience)
    echo "2. Testing POST /experiences (Create new experience)\n";
    $newExperience = [
        'title' => 'Test API Experience - Hiking Adventure',
        'description' => 'A thrilling hiking experience through scenic mountain trails. Perfect for adventure seekers looking to explore nature.',
        'price' => 75.00,
        'currency' => 'USD',
        'address' => '123 Mountain Trail Road',
        'city' => 'Boulder',
        'state' => 'Colorado',
        'country' => 'United States',
        'latitude' => 40.0150,
        'longitude' => -105.2705,
        'status' => 'draft',
        'images' => [
            'https://example.com/hiking1.jpg',
            'https://example.com/hiking2.jpg'
        ],
        'amenities' => [],
        'duration' => '4 hours',
        'activity_type' => 'adventure',
        'group_size' => 8,
        'difficulty_level' => 'moderate',
        'included_items' => [
            'Professional guide',
            'Safety equipment',
            'Water bottles',
            'Trail snacks'
        ],
        'requirements' => 'Participants should be in good physical condition and wear appropriate hiking boots.',
        'cancellation_policy' => 'flexible'
    ];
    
    $response = makeRequest('POST', "$baseUrl/experiences", $newExperience, $token);
    echo "Status Code: {$response['status_code']}\n";
    
    $createdExperienceId = null;
    if ($response['status_code'] === 201) {
        echo "✅ Successfully created new experience\n";
        $createdExperienceId = $response['body']['data']['id'] ?? null;
        echo "Created Experience ID: $createdExperienceId\n";
        echo "Title: " . ($response['body']['data']['title'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Failed to create experience\n";
        echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";

    if ($createdExperienceId) {
        // Test 3: GET /experiences/{id} (Get specific experience)
        echo "3. Testing GET /experiences/{$createdExperienceId} (Get specific experience)\n";
        $response = makeRequest('GET', "$baseUrl/experiences/$createdExperienceId", null, $token);
        echo "Status Code: {$response['status_code']}\n";
        if ($response['status_code'] === 200) {
            echo "✅ Successfully fetched specific experience\n";
            echo "Title: " . ($response['body']['data']['title'] ?? 'N/A') . "\n";
            echo "Price: $" . ($response['body']['data']['price'] ?? 'N/A') . "\n";
            echo "Duration: " . ($response['body']['data']['experience']['duration'] ?? 'N/A') . "\n";
        } else {
            echo "❌ Failed to fetch specific experience\n";
            echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";

        // Test 4: PUT /experiences/{id} (Update experience)
        echo "4. Testing PUT /experiences/{$createdExperienceId} (Update experience)\n";
        $updateData = [
            'title' => 'Updated API Experience - Advanced Hiking Adventure',
            'price' => 85.00,
            'difficulty_level' => 'challenging',
            'group_size' => 6,
            'status' => 'published'
        ];
        
        $response = makeRequest('PUT', "$baseUrl/experiences/$createdExperienceId", $updateData, $token);
        echo "Status Code: {$response['status_code']}\n";
        if ($response['status_code'] === 200) {
            echo "✅ Successfully updated experience\n";
            echo "Updated Title: " . ($response['body']['data']['title'] ?? 'N/A') . "\n";
            echo "Updated Price: $" . ($response['body']['data']['price'] ?? 'N/A') . "\n";
            echo "Updated Status: " . ($response['body']['data']['status'] ?? 'N/A') . "\n";
        } else {
            echo "❌ Failed to update experience\n";
            echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";

        // Test 5: DELETE /experiences/{id} (Delete experience)
        echo "5. Testing DELETE /experiences/{$createdExperienceId} (Delete experience)\n";
        $response = makeRequest('DELETE', "$baseUrl/experiences/$createdExperienceId", null, $token);
        echo "Status Code: {$response['status_code']}\n";
        if ($response['status_code'] === 200) {
            echo "✅ Successfully deleted experience\n";
        } else {
            echo "❌ Failed to delete experience\n";
            echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";

        // Test 6: Verify deletion by trying to fetch the deleted experience
        echo "6. Testing GET /experiences/{$createdExperienceId} (Verify deletion)\n";
        $response = makeRequest('GET', "$baseUrl/experiences/$createdExperienceId", null, $token);
        echo "Status Code: {$response['status_code']}\n";
        if ($response['status_code'] === 404) {
            echo "✅ Experience successfully deleted (404 Not Found)\n";
        } else {
            echo "❌ Experience may not have been deleted properly\n";
            echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }

    // Test 7: Test validation errors
    echo "7. Testing POST /experiences with invalid data (Validation test)\n";
    $invalidData = [
        'title' => '', // Required field empty
        'price' => -10, // Negative price
        'group_size' => 0, // Invalid group size
        'difficulty_level' => 'invalid_level' // Invalid difficulty level
    ];
    
    $response = makeRequest('POST', "$baseUrl/experiences", $invalidData, $token);
    echo "Status Code: {$response['status_code']}\n";
    if ($response['status_code'] === 422) {
        echo "✅ Validation working correctly (422 Unprocessable Entity)\n";
        if (isset($response['body']['errors'])) {
            echo "Validation errors found:\n";
            foreach ($response['body']['errors'] as $field => $errors) {
                echo "  - $field: " . implode(', ', $errors) . "\n";
            }
        }
    } else {
        echo "❌ Validation may not be working properly\n";
        echo "Response: " . json_encode($response['body'], JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";

    echo "=== Experience CRUD API Testing Complete ===\n";
    echo "\n";
    echo "Summary:\n";
    echo "- All CRUD operations (Create, Read, Update, Delete) tested\n";
    echo "- Validation testing included\n";
    echo "- Authentication with JWT token verified\n";
    echo "- Experience-specific fields tested\n";
    
} catch (Exception $e) {
    echo "❌ Error during testing: " . $e->getMessage() . "\n";
}

echo "\nTesting completed.\n";