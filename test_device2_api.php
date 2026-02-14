<?php
echo "=== TESTING API ENDPOINT FOR DEVICE 2 ===\n\n";

// Test Data untuk Device #7 (Ruangan B1)
$testData = [
    'device_id' => 'DEVICE_5VGP9BAM7C_1771067547',
    'temperature' => 26.5,
    'humidity' => 55.0
];

echo "Test 1: Direct PHP Test\n";
echo "Sending data for Device #7...\n";
echo json_encode($testData, JSON_PRETTY_PRINT) . "\n";

// Simulate HTTP POST request
$ch = curl_init('http://192.168.186.241:8000/api/monitoring/store');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\nResponse Status: HTTP $httpCode\n";
if ($response) {
    echo "Response Body:\n";
    $decoded = json_decode($response, true);
    if ($decoded) {
        echo json_encode($decoded, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $response . "\n";
    }
}

// Test 2: Check if data was stored
echo "\n\nTest 2: Verify Data Stored in Database\n";
$pdo = new PDO('mysql:host=localhost;dbname=monitoring_suhu_bayi', 'root', '');

$latest = $pdo->query('
    SELECT m.id, m.device_id, d.device_name, m.temperature, m.humidity, m.recorded_at
    FROM monitorings m
    JOIN devices d ON m.device_id = d.id
    WHERE d.id = 7
    ORDER BY m.id DESC
    LIMIT 2
')->fetchAll();

if (count($latest) > 0) {
    echo "✅ Data found for Device #7 (Ruangan B1):\n";
    foreach ($latest as $m) {
        echo "   ID: {$m['id']}, Temp: {$m['temperature']}°C, Humidity: {$m['humidity']}%, Time: {$m['recorded_at']}\n";
    }
} else {
    echo "❌ No data found for Device #7 (Ruangan B1)\n";
}

// Test 3: Check device ID mapping
echo "\n\nTest 3: Device ID Mapping\n";
$devices = $pdo->query('SELECT id, device_name, device_id FROM devices WHERE id IN (6, 7)')->fetchAll();
foreach ($devices as $d) {
    echo "Device #{$d['id']} ({$d['device_name']}): {$d['device_id']}\n";
}

// Test 4: Check API endpoint code
echo "\n\nTest 4: API Endpoint Response Messages\n";
echo "Checking if endpoint validates device_id correctly...\n";

// Test with invalid device_id
$invalidData = [
    'device_id' => 'INVALID_DEVICE_ID',
    'temperature' => 26.5,
    'humidity' => 55.0
];

$ch = curl_init('http://192.168.186.241:8000/api/monitoring/store');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($invalidData));
curl_setopt($ch, CURLOPT_TIMEOUT, 5);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLOPT_HTTP_CODE);
curl_close($ch);

echo "With invalid device_id - HTTP $httpCode\n";
if ($httpCode === 422) {
    echo "✅ API correctly validates device_id (422 Unprocessable Entity)\n";
} else {
    echo "Response: " . substr($response, 0, 200) . "...\n";
}

?>
