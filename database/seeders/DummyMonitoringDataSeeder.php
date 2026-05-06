<?php

namespace Database\Seeders;

use App\Models\Device;
use App\Models\Monitoring;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyMonitoringDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil device pertama, jika tidak ada, buat baru
        $device = Device::first();
        
        if (!$device) {
            $device = Device::create([
                'device_name' => 'Ruang Bayi #1',
                'location' => 'NICU Ward A',
                'device_id' => 'ruang_bayi_1'
            ]);
        }

        $this->command->info("Membuat data dummy 1 bulan untuk device: {$device->device_name}");

        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();
        
        $currentDate = clone $startDate;
        $records = [];
        
        // Buat data setiap 30 menit
        while ($currentDate <= $endDate) {
            $records[] = [
                'device_id' => $device->id,
                // Suhu ideal ruangan bayi antara 24°C - 26°C
                'temperature' => mt_rand(240, 265) / 10,
                // Kelembapan ideal ruangan bayi antara 45% - 55%
                'humidity' => mt_rand(450, 550) / 10,
                'status' => 'Aman',
                'recorded_at' => $currentDate->format('Y-m-d H:i:s'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Insert per chunk untuk mencegah memory limit
            if (count($records) >= 500) {
                Monitoring::insert($records);
                $records = [];
            }

            $currentDate->addMinutes(30);
        }

        // Insert sisa data
        if (count($records) > 0) {
            Monitoring::insert($records);
        }

        $this->command->info("Berhasil menambahkan data dummy selama 1 bulan dengan suhu aman untuk bayi.");
    }
}
