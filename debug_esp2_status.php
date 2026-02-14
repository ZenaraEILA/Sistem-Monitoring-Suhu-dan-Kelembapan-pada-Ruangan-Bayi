<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';

use App\Models\Device;
use App\Models\Monitoring;

echo "================================\n";
echo "DEBUG: ESP8266 Device 2 Status\n";
echo "================================\n\n";

// Get Device 2 (Ruangan B1)
$device = Device::find(7);

if (!$device) {
    echo "❌ Device #7 not found\n";
    exit(1);
}

echo "Device Info:\n";
echo "  ID: {$device->id}\n";
echo "  Name: {$device->device_name}\n";
echo "  Location: {$device->location}\n";
echo "  Device ID: {$device->device_id}\n";
echo "\n";

// Check latest data
$latest = Monitoring::where('device_id', $device->id)
    ->latest('recorded_at')
    ->first();

if ($latest) {
    echo "Latest Data:\n";
    echo "  Temperature: {$latest->temperature}°C\n";
    echo "  Humidity: {$latest->humidity}%\n";
    echo "  Time: {$latest->recorded_at}\n";
    
    // Calculate seconds ago
    $now = new DateTime();
    $diff = $now->diff(new DateTime($latest->recorded_at->toDateTimeString()));
    $seconds = ($diff->days * 86400) + ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
    echo "  Seconds Ago: $seconds sec\n";
} else {
    echo "⚠️  No data recorded from this device yet\n";
}

// Count total records
$count = Monitoring::where('device_id', $device->id)->count();
echo "\nTotal Records: $count\n";

// Check device status
$status = $device->deviceStatus;
if ($status) {
    echo "\nDevice Status:\n";
    echo "  ESP Status: {$status->esp_status}\n";
    echo "  Updated At: {$status->updated_at}\n";
} else {
    echo "\n⚠️  No device status record\n";
}

// Check ESP connection timeout
echo "\n--- Connection Analysis ---\n";
if ($latest) {
    $dbNow = \DB::selectOne('SELECT NOW() as db_time');
    $serverTime = new DateTime($dbNow->db_time);
    $lastTime = $latest->recorded_at;
    
    $diff = $serverTime->diff($lastTime);
    $secondsAgo = ($diff->days * 86400) + ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
    
    echo "Server Time: {$dbNow->db_time}\n";
    echo "Last Data Time: {$lastTime}\n";
    echo "Seconds Ago: $secondsAgo\n";
    
    if ($secondsAgo <= 10) {
        echo "Status: ✅ ONLINE (< 10 sec)\n";
    } elseif ($secondsAgo <= 300) {
        echo "Status: ⚠️  OFFLINE (10-300 sec)\n";
    } else {
        echo "Status: ❌ DISCONNECTED (> 300 sec)\n";
    }
} else {
    echo "Status: ❌ NO DATA - ESP never connected\n";
}

echo "\n";
?>
