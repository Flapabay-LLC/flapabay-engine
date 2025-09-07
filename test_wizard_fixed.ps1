# Test wizard listing creation without host_id requirement
# Get fresh token
$loginBody = @{ email = "mikakovac@gmail.com"; password = "password" } | ConvertTo-Json
try {
    $loginResponse = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/login" -Method POST -Headers @{"Content-Type" = "application/json"} -Body $loginBody
    $loginData = $loginResponse.Content | ConvertFrom-Json
    $token = $loginData.access_token
    Write-Host "Login successful! Using fresh token."
} catch {
    Write-Host "Login failed: $($_.Exception.Message)"
    exit 1
}

$headers = @{
    "Authorization" = "Bearer $token"
    "Content-Type" = "application/json"
}

$body = @{
    title = "Test Villa"
    description = "A beautiful test property"
    property_type_id = 1
    price_per_night = 150
    currency = "USD"
    num_of_bedrooms = 2
    num_of_bathrooms = 1
    maximum_guests = 4
    address = "123 Test St"
    city = "Test City"
    country = "USA"
    finalize = $false
} | ConvertTo-Json

Write-Host "Testing wizard listing creation..."
Write-Host "URL: http://localhost:8000/api/v1/wizard-listings"
Write-Host "Method: POST"
Write-Host "Body: $body"
Write-Host ""

try {
    $response = Invoke-WebRequest -Uri "http://localhost:8000/api/v1/wizard-listings" -Method POST -Headers $headers -Body $body
    Write-Host "SUCCESS!"
    Write-Host "Status Code: $($response.StatusCode)"
    Write-Host "Response Body:"
    Write-Host $response.Content
} catch {
    Write-Host "ERROR!"
    Write-Host "Exception: $($_.Exception.Message)"
    
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "Status Code: $statusCode"
        
        $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
        $responseBody = $reader.ReadToEnd()
        $reader.Close()
        
        Write-Host "Response Body:"
        Write-Host $responseBody
    }
}

Write-Host ""
Write-Host "Test completed."