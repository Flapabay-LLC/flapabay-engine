# Debug authentication step by step
Write-Host "=== Authentication Debug ==="

# Step 1: Login
Write-Host "Step 1: Attempting login..."
$loginBody = '{"email": "mikakovac@gmail.com", "password": "password"}'

try {
    $loginResponse = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/login" -Method POST -Headers @{"Content-Type" = "application/json"} -Body $loginBody
    Write-Host "Login Status: $($loginResponse.StatusCode)"
    Write-Host "Login Response Content: $($loginResponse.Content)"
    
    $loginData = $loginResponse.Content | ConvertFrom-Json
    Write-Host "Parsed login data: $($loginData | ConvertTo-Json)"
    $token = $loginData.token
    
    if ($token) {
        Write-Host "Token received: $($token.Substring(0,50))..."
        
        # Step 2: Test wizard-listings endpoint
        Write-Host "\nStep 2: Testing wizard-listings endpoint..."
        $headers = @{
            "Authorization" = "Bearer $token"
            "Content-Type" = "application/json"
        }
        
        $testBody = '{
            "title": "Test listing",
            "description": "A test listing",
            "listing_type_id": 1,
            "price_per_night": 100,
            "currency": "USD",
            "num_of_bedrooms": 2,
            "num_of_bathrooms": 1,
            "maximum_guests": 4,
            "address": "123 Test St",
            "city": "Test City",
            "country": "USA",
            "finalize": false
        }'
        
        try {
            $wizardResponse = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/wizard-listings" -Method POST -Headers $headers -Body $testBody
            Write-Host "SUCCESS! Status: $($wizardResponse.StatusCode)"
            Write-Host "Response: $($wizardResponse.Content)"
        } catch {
            Write-Host "FAILED! Error: $($_.Exception.Message)"
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
    } else {
        Write-Host "No token received!"
    }
    
} catch {
    Write-Host "Login failed: $($_.Exception.Message)"
    if ($_.Exception.Response) {
        $stream = $_.Exception.Response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        $responseBody = $reader.ReadToEnd()
        $reader.Close()
        
        Write-Host "Login Response: $responseBody"
    }
}

Write-Host "\n=== Debug Complete ==="