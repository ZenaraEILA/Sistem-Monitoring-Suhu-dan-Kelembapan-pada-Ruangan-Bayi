<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Monitoring;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MonitoringSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed monitoring data for testing
     */
    public function run(): void
    {
        // Create or get device
        $device = Device::firstOrCreate(
            ['device_name' => 'Ruang Bayi #1'],
            [
                'location' => 'NICU Ward',
                'device_id' => strtolower(str_replace(' ', '_', 'Ruang Bayi #1')) . '_' . time(),
            ]
        );

        // Delete existing monitoring data for this device
        Monitoring::where('device_id', $device->id)->delete();

        // Generate 24 hours of monitoring data (every 5 minutes)
        $startTime = Carbon::now()->subHours(24)->startOfHour();
        $currentTime = $startTime;
        $endTime = Carbon::now();

        $statuses = ['Aman', 'Tidak Aman'];
        $dataCount = 0;

        while ($currentTime <= $endTime) {
            // Generate realistic baby monitoring data
            // Normal baby temp: 36.5 - 37.5°C with small variations
            $baseTemp = 37.0;
            $tempVariation = sin($currentTime->timestamp / 3600) * 0.5 + (rand(-10, 10) / 10);
            $temperature = round($baseTemp + $tempVariation, 2);

            // Normal room humidity: 40 - 60%
            $baseHumidity = 50;
            $humVariation = sin($currentTime->timestamp / 7200) * 8 + (rand(-5, 5));
            $humidity = max(40, min(70, round($baseHumidity + $humVariation, 2)));

            // Determine status based on temperature
            // Aman (Safe): 36.5 - 37.5°C
            // Tidak Aman (Not Safe): < 36.5 or > 37.5°C
            if ($temperature >= 38 || $temperature <= 36) {
                $status = 'Tidak Aman';
                $isEmergency = $temperature >= 38.5 || $temperature <= 35.5;
            } else {
                $status = 'Aman';
                $isEmergency = false;
            }

            Monitoring::create([
                'device_id' => $device->id,
                'temperature' => $temperature,
                'humidity' => $humidity,
                'status' => $status,
                'recorded_at' => $currentTime,
                'action_note' => $status !== 'Aman' ? 'Monitoring diperlukan' : null,
                'consecutive_unsafe_count' => $status !== 'Aman' ? 1 : 0,
                'is_emergency' => $isEmergency,
            ]);

            $currentTime->addMinutes(5);
            $dataCount++;
        }

        $this->command->info("✅ Dummy monitoring data created: {$dataCount} records untuk device: {$device->device_name}");
    }
}
