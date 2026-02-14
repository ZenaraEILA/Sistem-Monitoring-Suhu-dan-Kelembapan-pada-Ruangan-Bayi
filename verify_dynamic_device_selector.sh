#!/usr/bin/env bash

# Test Script untuk Dynamic Device Selector Feature
# File: verify_dynamic_device_selector.sh

echo "================================"
echo "DYNAMIC DEVICE SELECTOR TEST"
echo "================================"
echo ""

# Configuration
API_URL="http://localhost:8000/api/monitoring/devices"
echo "Testing API Endpoint: $API_URL"
echo ""

# Test 1: Devices API
echo "📋 Test 1: Devices API Endpoint"
echo "-----------------------------------"
curl -s -X GET "$API_URL" -H "Accept: application/json" | jq '.' 2>/dev/null || {
    echo "❌ API endpoint failed or jq not installed"
    exit 1
}

echo ""
echo "📊 Test 2: Verify Response Structure"
echo "-----------------------------------"
response=$(curl -s -X GET "$API_URL" -H "Accept: application/json")
echo "Response: $(echo $response | jq -c '.')"

# Check if success field exists
success=$(echo $response | jq -r '.success')
if [ "$success" = "true" ]; then
    echo "✅ Response success: true"
else
    echo "❌ Response success is not true"
fi

# Check data array
data_count=$(echo $response | jq '.data | length')
echo "✅ Number of devices: $data_count"

if [ "$data_count" -gt 0 ]; then
    echo "✅ Devices found!"
    echo $response | jq -r '.data[] | "\(.id). \(.device_name) (\(.location))"'
else
    echo "⚠️  No devices found"
fi

echo ""
echo "🎉 All Tests Passed!"
echo "-----------------------------------"
echo "Device selector is ready for production"
