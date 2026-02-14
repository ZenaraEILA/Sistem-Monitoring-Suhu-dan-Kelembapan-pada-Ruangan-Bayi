<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Monitoring;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MonitoringController extends Controller
{
    /**
     * Store a newly created monitoring record from ESP sensor.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|exists:devices,device_id',
            'temperature' => 'required|numeric|between:-50,60',
            'humidity' => 'required|numeric|between:0,100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $device = Device::where('device_id', $request->device_id)->first();

        // Determine status based on temperature and humidity
        $status = 'Aman';
        if ($request->temperature < 15 || $request->temperature > 30) {
            $status = 'Tidak Aman';
        }
        if ($request->humidity < 35 || $request->humidity > 60) {
            $status = 'Tidak Aman';
        }

        $monitoring = Monitoring::create([
            'device_id' => $device->id,
            'temperature' => $request->temperature,
            'humidity' => $request->humidity,
            'status' => $status,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'message' => 'Data monitoring berhasil disimpan',
            'data' => $monitoring,
        ], 201);
    }

    /**
     * Get latest monitoring data for a device.
     */
    public function getLatest($deviceId)
    {
        $device = Device::where('device_id', $deviceId)->first();

        if (!$device) {
            return response()->json(['message' => 'Device not found'], 404);
        }

        $monitoring = Monitoring::where('device_id', $device->id)
            ->latest('recorded_at')
            ->first();

        if (!$monitoring) {
            return response()->json(['message' => 'No monitoring data found'], 404);
        }

        return response()->json(['data' => $monitoring], 200);
    }

    /**
     * Get all latest monitoring data for realtime dashboard
     * Includes ESP connection status (based on last ping)
     */
    public function getRealtimeDashboard()
    {
        $devices = Device::with(['monitorings' => function ($query) {
            $query->latest('recorded_at')->limit(1);
        }])->get();

        $data = [];
        foreach ($devices as $device) {
            $latestMonitoring = $device->monitorings->first();
            
            // Check connection status: connected jika last update < 2 menit
            $isConnected = false;
            $lastUpdateTime = null;
            
            if ($latestMonitoring) {
                $lastUpdateTime = $latestMonitoring->recorded_at;
                $minutesDifference = now()->diffInMinutes($lastUpdateTime);
                $isConnected = $minutesDifference < 2; // Connected jika < 2 menit
            }

            $data[] = [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
                'location' => $device->location,
                'is_connected' => $isConnected,
                'connection_status' => $isConnected ? 'TERHUBUNG' : 'TIDAK TERHUBUNG',
                'last_update' => $lastUpdateTime ? $lastUpdateTime->toIso8601String() : null,
                'minutes_ago' => $lastUpdateTime ? now()->diffInMinutes($lastUpdateTime) : null,
                'temperature' => $latestMonitoring ? $latestMonitoring->temperature : null,
                'humidity' => $latestMonitoring ? $latestMonitoring->humidity : null,
                'status' => $latestMonitoring ? $latestMonitoring->status : null,
            ];
        }

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ], 200);
    }

    /**
     * Get hourly aggregated data for charts (real-time)
     * 
     * Query params:
     * - device_id: ID device (required)
     * - date: Tanggal (format: Y-m-d, default: today)
     */
    public function getHourlyChartData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|integer|exists:devices,id',
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deviceId = $request->device_id;
        $date = $request->date ? \Carbon\Carbon::parse($request->date) : \Carbon\Carbon::today();

        // Get hourly data from model
        $hourlyData = Monitoring::getHourlyData($deviceId, $date);

        // Format data untuk chart
        $chartData = [
            'hours' => $hourlyData->pluck('hour')->map(function ($hour) {
                return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            })->toArray(),
            'avg_temperatures' => $hourlyData->pluck('avg_temp')->toArray(),
            'max_temperatures' => $hourlyData->pluck('max_temp')->toArray(),
            'min_temperatures' => $hourlyData->pluck('min_temp')->toArray(),
            'avg_humidities' => $hourlyData->pluck('avg_humidity')->toArray(),
            'max_humidities' => $hourlyData->pluck('max_humidity')->toArray(),
            'min_humidities' => $hourlyData->pluck('min_humidity')->toArray(),
            'timestamps' => $hourlyData->pluck('hour')->map(function ($hour) {
                return (int) $hour;
            })->toArray(),
        ];

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'date' => $date->format('Y-m-d'),
            'device_id' => $deviceId,
            'data' => $chartData,
        ], 200);
    }

    /**
     * Get hourly chart data V2 - DYNAMIC (OPSI 1: Real-time murni, no padding)
     * Hanya menampilkan jam yang memiliki data
     * Tidak ada nilai 0 sebelum/sesudah data pertama masuk
     * 
     * Query params:
     * - device_id: ID device (required)
     * - date: Tanggal (format: Y-m-d, default: today)
     * 
     * Response: 
     * {
     *   "success": true,
     *   "data": {
     *     "start_hour": 8,
     *     "end_hour": 16,
     *     "first_data_time": "2026-02-14T08:15:22+07:00",
     *     "last_data_time": "2026-02-14T16:45:10+07:00",
     *     "hours": [8, 9, 10, ...],
     *     "labels": ["08:00", "09:00", ...],
     *     "avg_temperatures": [27.5, 28.2, ...],
     *     "max_temperatures": [29.5, 30.2, ...],
     *     "min_temperatures": [25.5, 26.2, ...],
     *     "avg_humidities": [55, 58, ...],
     *   }
     * }
     */
    public function getHourlyChartDataDynamic(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|integer|exists:devices,id',
            'date' => 'nullable|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deviceId = $request->device_id;
        $date = $request->date ? \Carbon\Carbon::parse($request->date) : \Carbon\Carbon::today();

        // Get hourly data from model
        $hourlyData = Monitoring::getHourlyData($deviceId, $date);

        // Jika tidak ada data, return empty
        if ($hourlyData->isEmpty()) {
            return response()->json([
                'success' => true,
                'timestamp' => now()->toIso8601String(),
                'date' => $date->format('Y-m-d'),
                'device_id' => $deviceId,
                'data' => [
                    'start_hour' => null,
                    'end_hour' => null,
                    'first_data_time' => null,
                    'last_data_time' => null,
                    'data_count' => 0,
                    'hours' => [],
                    'labels' => [],
                    'avg_temperatures' => [],
                    'max_temperatures' => [],
                    'min_temperatures' => [],
                    'avg_humidities' => [],
                    'max_humidities' => [],
                    'min_humidities' => [],
                ]
            ], 200);
        }

        // Get time range info
        $firstData = Monitoring::where('device_id', $deviceId)
            ->whereDate('recorded_at', $date)
            ->oldest('recorded_at')
            ->first();
        
        $lastData = Monitoring::where('device_id', $deviceId)
            ->whereDate('recorded_at', $date)
            ->latest('recorded_at')
            ->first();

        $startHour = $firstData ? $firstData->recorded_at->hour : 0;
        $endHour = $lastData ? $lastData->recorded_at->hour : 0;

        // Format data untuk chart (HANYA jam yang ada data)
        $chartData = [
            'start_hour' => (int) $startHour,
            'end_hour' => (int) $endHour,
            'first_data_time' => $firstData ? $firstData->recorded_at->toIso8601String() : null,
            'last_data_time' => $lastData ? $lastData->recorded_at->toIso8601String() : null,
            'data_count' => count($hourlyData),
            'hours' => $hourlyData->pluck('hour')->map(fn($h) => (int) $h)->toArray(),
            'labels' => $hourlyData->pluck('hour')->map(function ($hour) {
                return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            })->toArray(),
            'avg_temperatures' => $hourlyData->pluck('avg_temp')->toArray(),
            'max_temperatures' => $hourlyData->pluck('max_temp')->toArray(),
            'min_temperatures' => $hourlyData->pluck('min_temp')->toArray(),
            'avg_humidities' => $hourlyData->pluck('avg_humidity')->toArray(),
            'max_humidities' => $hourlyData->pluck('max_humidity')->toArray(),
            'min_humidities' => $hourlyData->pluck('min_humidity')->toArray(),
        ];

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'date' => $date->format('Y-m-d'),
            'device_id' => $deviceId,
            'data' => $chartData,
        ], 200);
    }

    /**
     * Get real-time latest monitoring data for live dashboard
     * Update setiap 1 detik dengan status ESP online/offline
     * 
     * GET /api/monitoring/realtime/latest
     */
    public function getRealtimeLatest()
    {
        $devices = Device::with(['monitorings' => function ($query) {
            $query->latest('recorded_at')->limit(1);
        }])->get();

        $data = [];
        foreach ($devices as $device) {
            $latestMonitoring = $device->monitorings->first();
            
            // ESP Status: ONLINE jika last update < 5 detik, OFFLINE jika >= 5 detik
            $isOnline = false;
            $lastUpdateTime = null;
            $secondsAgo = null;
            $statusMessage = 'OFFLINE';
            
            if ($latestMonitoring) {
                $lastUpdateTime = $latestMonitoring->recorded_at;
                $secondsAgo = now()->diffInSeconds($lastUpdateTime);
                $isOnline = $secondsAgo < 5; // Online jika < 5 detik
                $statusMessage = $isOnline ? 'ONLINE' : 'OFFLINE';
            }

            // Determine status warna untuk temperature & humidity
            $tempStatus = 'safe';  // hijau
            $humidityStatus = 'safe'; // biru
            
            if ($latestMonitoring) {
                if ($latestMonitoring->temperature >= 30) {
                    $tempStatus = 'warning'; // kuning
                }
                if ($latestMonitoring->temperature > 35) {
                    $tempStatus = 'danger'; // merah
                }
                
                if ($latestMonitoring->humidity >= 60) {
                    $humidityStatus = 'warning'; // orange
                }
            }

            $data[] = [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
                'location' => $device->location,
                // Temperature data
                'temperature' => $latestMonitoring ? $latestMonitoring->temperature : null,
                'temp_status' => $tempStatus, // safe, warning, danger
                // Humidity data
                'humidity' => $latestMonitoring ? $latestMonitoring->humidity : null,
                'humidity_status' => $humidityStatus, // safe, warning
                // ESP Status
                'esp_online' => $isOnline,
                'esp_status' => $statusMessage,
                'seconds_ago' => $secondsAgo,
                'last_update' => $lastUpdateTime ? $lastUpdateTime->toIso8601String() : null,
                // Overall monitoring status
                'monitoring_status' => $latestMonitoring ? $latestMonitoring->status : null,
            ];
        }

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'data' => $data,
        ], 200);
    }
}
