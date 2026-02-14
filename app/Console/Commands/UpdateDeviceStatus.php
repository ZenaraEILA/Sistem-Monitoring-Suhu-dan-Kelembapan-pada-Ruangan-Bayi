<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Models\DeviceStatus;
use Illuminate\Console\Command;

class UpdateDeviceStatus extends Command
{
    protected $signature = 'device:update-status {--timeout=10}';
    protected $description = 'Check and update device online/offline status in device_statuses table';

    public function handle()
    {
        $timeout = (int)$this->option('timeout');
        $devices = Device::all();

        $this->info("🔄 Updating device statuses (timeout: {$timeout}s)...\n");

        foreach ($devices as $device) {
            DeviceStatus::checkDeviceStatus($device, $timeout);
            
            $status = $device->deviceStatus;
            if ($status) {
                $icon = $status->status === 'online' ? '✅' : '❌';
                $this->line("$icon Device #{$device->id} ({$device->device_name}): {$status->status}");
            }
        }

        $this->info("\n✅ Device statuses updated successfully!");
    }
}
