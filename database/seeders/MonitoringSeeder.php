<?php

namespace Database\Seeders;

use App\Models\Device;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MonitoringSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Create 5 empty devices (tanpa monitoring data)
     */
    public function run(): void
    {
        // Define 5 devices dengan predictable device_id
        $deviceConfigs = [
            ['name' => 'Ruang Bayi #1', 'location' => 'NICU Ward A', 'device_id' => 'ruang_bayi_1'],
            ['name' => 'Ruang Bayi #2', 'location' => 'NICU Ward B', 'device_id' => 'ruang_bayi_2'],
            ['name' => 'Ruang Bayi #3', 'location' => 'Recovery Room', 'device_id' => 'ruang_bayi_3'],
            ['name' => 'Ruang Bayi #4', 'location' => 'Observation Ward', 'device_id' => 'ruang_bayi_4'],
            ['name' => 'Ruang Bayi #5', 'location' => 'Monitoring Room', 'device_id' => 'ruang_bayi_5'],
        ];

        foreach ($deviceConfigs as $config) {
            // Create device with predictable device_id
            Device::firstOrCreate(
                ['device_id' => $config['device_id']],
                [
                    'device_name' => $config['name'],
                    'location' => $config['location'],
                ]
            );
            
            $this->command->info("✅ Device '{$config['name']}' created with device_id: {$config['device_id']}");
        }

        $this->command->info("✅ Total 5 devices created (kosong)");
    }
}
