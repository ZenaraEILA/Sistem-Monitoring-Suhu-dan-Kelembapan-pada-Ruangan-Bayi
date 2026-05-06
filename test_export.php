<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $device = App\Models\Device::first();
    $monitorings = App\Models\Monitoring::where('device_id', $device->id)->limit(10)->get();
    $date = Carbon\Carbon::now();
    
    $excel = App\Services\ExcelExportService::export($device, $monitorings, $date, $date, 'daily', 'test');
    echo "EXCEL OK\n";
} catch (\Exception $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine();
}
