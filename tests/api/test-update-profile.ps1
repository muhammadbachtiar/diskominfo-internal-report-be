# Test Update Profile API (PowerShell)
# Make sure to replace YOUR_TOKEN with actual Bearer token

$BASE_URL = "http://localhost:8000/api/v1"
$TOKEN = "YOUR_TOKEN_HERE"

$headers = @{
    "Authorization" = "Bearer $TOKEN"
    "Accept" = "application/json"
    "Content-Type" = "application/json"
}

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Testing Update Profile API" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Test 1: Get Current User Data
Write-Host "Test 1: Get Current User Data" -ForegroundColor Yellow
Write-Host "------------------------------------------" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$BASE_URL/auth" -Method GET -Headers $headers
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}
Write-Host "`n"

# Test 2: Update Name Only (Partial Update)
Write-Host "Test 2: Update Name Only (Partial Update)" -ForegroundColor Yellow
Write-Host "------------------------------------------" -ForegroundColor Yellow
try {
    $body = @{
        name = "Updated Name via PowerShell Test"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$BASE_URL/auth" -Method PATCH -Headers $headers -Body $body
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}
Write-Host "`n"

# Test 3: Update Email Only
Write-Host "Test 3: Update Email Only" -ForegroundColor Yellow
Write-Host "------------------------------------------" -ForegroundColor Yellow
try {
    $body = @{
        email = "updatedemail@example.com"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$BASE_URL/auth" -Method PATCH -Headers $headers -Body $body
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}
Write-Host "`n"

# Test 4: Update Password
Write-Host "Test 4: Update Password" -ForegroundColor Yellow
Write-Host "------------------------------------------" -ForegroundColor Yellow
try {
    $body = @{
        password = "newpassword123"
        password_confirmation = "newpassword123"
    } | ConvertTo-Json
    
    $response = Invoke-RestMethod -Uri "$BASE_URL/auth" -Method PATCH -Headers $headers -Body $body
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}
Write-Host "`n"

# Test 5: Get User Data After Updates
Write-Host "Test 5: Get User Data After Updates" -ForegroundColor Yellow
Write-Host "------------------------------------------" -ForegroundColor Yellow
try {
    $response = Invoke-RestMethod -Uri "$BASE_URL/auth" -Method GET -Headers $headers
    $response | ConvertTo-Json -Depth 10
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
}
Write-Host "`n"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Tests Completed!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Note: For avatar upload test, use this PowerShell command:" -ForegroundColor Yellow
Write-Host ""
Write-Host '$file = Get-Item "C:\path\to\image.jpg"' -ForegroundColor Gray
Write-Host '$form = @{' -ForegroundColor Gray
Write-Host '    avatar = $file' -ForegroundColor Gray
Write-Host '}' -ForegroundColor Gray
Write-Host 'Invoke-WebRequest -Uri "http://localhost:8000/api/v1/auth" `' -ForegroundColor Gray
Write-Host '    -Method PATCH `' -ForegroundColor Gray
Write-Host '    -Headers @{"Authorization"="Bearer $TOKEN"} `' -ForegroundColor Gray
Write-Host '    -Form $form' -ForegroundColor Gray
