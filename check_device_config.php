<?php
$pdo = new PDO('mysql:host=localhost;dbname=monitoring_suhu_bayi', 'root', '');

echo "=== DEVICE CONFIGURATION CHECK ===\n\n";

// Check all devices
echo "1. All Devices in Database:\n";
$devices = $pdo->query('SELECT id, device_id, device_name, location FROM devices')->fetchAll();
foreach ($devices as $d) {
    echo "   Device #{$d['id']}: {$d['device_name']} ({$d['location']})\n";
    echo "      Device ID: {$d['device_id']}\n";
}

// Check device statuses
echo "\n2. Device Status Records:\n";
$statuses = $pdo->query('SELECT device_id, status, last_data_at FROM device_statuses')->fetchAll();
foreach ($statuses as $s) {
    echo "   Device #{$s['device_id']}: " . strtoupper($s['status']) . "\n";
}

// Check latest monitoring for each device
echo "\n3. Latest Data per Device:\n";
$latest_per_device = $pdo->query('
    SELECT DISTINCT m.device_id, d.device_name, m.temperature, m.humidity, m.recorded_at
    FROM monitorings m
    JOIN devices d ON m.device_id = d.id
    WHERE m.id IN (
        SELECT MAX(id) FROM monitorings GROUP BY device_id
    )
    ORDER BY m.device_id
')->fetchAll();
foreach ($latest_per_device as $m) {
    echo "   Device #{$m['device_id']} ({$m['device_name']}): {$m['temperature']}°C @ {$m['recorded_at']}\n";
}

// Check if there are devices without recent data
echo "\n4. Devices WITHOUT Recent Data (30 min+):\n";
$old_devices = $pdo->query('
    SELECT d.id, d.device_name, MAX(m.recorded_at) as last_data
    FROM devices d
    LEFT JOIN monitorings m ON d.id = m.device_id
    GROUP BY d.id
    HAVING MAX(m.recorded_at) IS NULL OR MAX(m.recorded_at) < DATE_SUB(NOW(), INTERVAL 30 MINUTE)
')->fetchAll();
if (count($old_devices) > 0) {
    foreach ($old_devices as $d) {
        echo "   Device #{$d['id']} ({$d['device_name']}): " . ($d['last_data'] ? $d['last_data'] : "Never") . "\n";
    }
} else {
    echo "   None - all devices have recent data\n";
}

// Check database server time
echo "\n5. Server Time:\n";
$time = $pdo->query('SELECT NOW() as time')->fetch();
echo "   " . $time['time'] . "\n";

?>
