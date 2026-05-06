<?php
$url = 'http://192.168.62.241:8000/api/monitoring/store';
$data = [
    'device_id' => 'DEVICE_LCF7P6RQYR_1777015359',
    'temperature' => 30.5,
    'humidity' => 50,
    'status_kipas_1' => 'ON',
    'status_kipas_2' => 'OFF',
    'status_lampu_biru' => 'OFF',
    'status_lampu_merah' => 'OFF',
    'status_penghangat' => 'OFF'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // to capture HTTP errors
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "HTTP Response: " . $http_response_header[0] . "\n";
echo "Body: " . $result . "\n";
