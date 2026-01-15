#!/bin/bash

# Test Update Profile API
# Make sure to replace YOUR_TOKEN with actual Bearer token

BASE_URL="http://localhost:8000/api/v1"
TOKEN="YOUR_TOKEN_HERE"

echo "=========================================="
echo "Testing Update Profile API"
echo "=========================================="
echo ""

# Test 1: Get Current User Data
echo "Test 1: Get Current User Data"
echo "------------------------------------------"
curl -X GET "$BASE_URL/auth" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

# Test 2: Update Name Only (Partial Update)
echo "Test 2: Update Name Only (Partial Update)"
echo "------------------------------------------"
curl -X PATCH "$BASE_URL/auth" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Updated Name via API Test"
  }'
echo -e "\n\n"

# Test 3: Update Email Only
echo "Test 3: Update Email Only"
echo "------------------------------------------"
curl -X PATCH "$BASE_URL/auth" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "newemail@example.com"
  }'
echo -e "\n\n"

# Test 4: Get User Data After Update
echo "Test 4: Get User Data After Update"
echo "------------------------------------------"
curl -X GET "$BASE_URL/auth" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
echo -e "\n\n"

echo "=========================================="
echo "Tests Completed!"
echo "=========================================="
echo ""
echo "Note: For avatar upload test, use Postman or similar tool with multipart/form-data"
echo "Example:"
echo "  curl -X PATCH \"$BASE_URL/auth\" \\"
echo "    -H \"Authorization: Bearer \$TOKEN\" \\"
echo "    -F \"avatar=@/path/to/image.jpg\""
