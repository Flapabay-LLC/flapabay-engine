# Final test of wizard listing creation
Write-Host "Testing wizard listing creation with host_id fix..."

# Login and get token
$loginBody = '{"email": "mikakovac@gmail.com", "password": "password"}'
$loginResponse = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/login" -Method POST -Headers @{"Content-Type" = "application/json"} -Body $loginBody
$loginData = $loginResponse.Content | ConvertFrom-Json
$token = $loginData.token

Write-Host "Token obtained successfully"

# Test wizard listing creation
$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
}

$testBody = '{
    "title": "Beautiful Test Villa",
    "description": "A stunning test property for validation",
    "property_type_id": 1,
    "price_per_night": 150,
    "currency": "USD",
    "num_of_bedrooms": 3,
    "num_of_bathrooms": 2,
    "maximum_guests": 6,
    "address": "123 Test Avenue",
    "city": "Test City",
    "country": "USA",
    "finalize": false
}'

Write-Host "Sending request to wizard-listings endpoint..."

try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/wizard-listings" -Method POST -Headers $headers -Body $testBody
    Write-Host "\n=== SUCCESS! ==="
    Write-Host "Status Code: $($response.StatusCode)"
    Write-Host "Response Body:"
    Write-Host $response.Content
    Write-Host "\n=== host_id validation error has been FIXED! ==="
} catch {
    Write-Host "\n=== ERROR ==="
    Write-Host "Exception: $($_.Exception.Message)"
    
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "Status Code: $statusCode"
        
        $stream = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        $responseBody = $reader.ReadToEnd()
        $reader.Close()
        
        Write-Host "Response Body: $responseBody"
    }
}

Write-Host "\nTest completed."