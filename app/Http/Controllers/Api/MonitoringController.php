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

        // Get database server time (not local PHP time)
        $dbNow = \DB::selectOne('SELECT NOW() as db_time');
        $serverTime = new \DateTime($dbNow->db_time);

        $data = [];
        foreach ($devices as $device) {
            $latestMonitoring = $device->monitorings->first();
            
            // Check connection status using database time
            // Connected jika last update < 10 detik (timeout)
            $isConnected = false;
            $lastUpdateTime = null;
            $minutesAgo = null;
            
            if ($latestMonitoring) {
                $lastUpdateTime = $latestMonitoring->recorded_at;
                
                // Calculate difference using database server time (not PHP local time)
                $diff = $serverTime->diff($lastUpdateTime);
                $secondsAgo = ($diff->days * 86400) + ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                $minutesAgo = round($secondsAgo / 60, 2);
                
                // Device terhubung jika data masuk dalam 10 detik
                // (sesuai dengan timeout di getRealtimeLatest)
                $isConnected = $secondsAgo <= 10;
            }

            $data[] = [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
                'location' => $device->location,
                'is_connected' => $isConnected,
                'connection_status' => $isConnected ? 'TERHUBUNG' : 'TIDAK TERHUBUNG',
                'last_update' => $lastUpdateTime ? $lastUpdateTime->toIso8601String() : null,
                'minutes_ago' => $minutesAgo !== null ? max(0, (int)$minutesAgo) : null,
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
     * Update setiap 1 detik dengan status ESP online/offline BERBASIS TIMESTAMP
     * 
     * GET /api/monitoring/realtime/latest?timeout=10
     * timeout: Detik untuk menganggap device OFFLINE (default: 10 detik)
     * 
     * Response:
     * {
     *   "success": true,
     *   "timestamp": "2026-02-14T10:30:45+07:00",
     *   "data": [
     *     {
     *       "id": 1,
     *       "device_name": "Ruang Bayi #1",
     *       "esp_online": true,
     *       "esp_status": "ONLINE | OFFLINE | DISCONNECTED",
     *       "seconds_ago": 3,
     *       "last_update": "2026-02-14T10:30:42+07:00",
     *       "temperature": 27.5,
     *       "humidity": 55,
     *       ...
     *     }
     *   ]
     * }
     */
    public function getRealtimeLatest(Request $request)
    {
        // Support filtering by device_id if provided
        $deviceId = $request->query('device_id');
        $timeoutSeconds = (int)$request->query('timeout', 10); // Default 10 detik
        
        $query = Device::with(['monitorings' => function ($query) {
            $query->latest('recorded_at')->limit(1); // Selalu gunakan recorded_at
        }]);
        
        // Filter by device_id if provided
        if ($deviceId) {
            $query->where('id', $deviceId);
        }
        
        $devices = $query->get();
        
        // Get current time from DATABASE (bukan local PHP time yang bisa berbeda timezone)
        $dbNow = \DB::selectOne('SELECT NOW() as db_time');
        $serverTime = new \DateTime($dbNow->db_time);
        
        $data = [];

        foreach ($devices as $device) {
            $latestMonitoring = $device->monitorings->first();
            
            // ESP Status detection: ONLINE jika recorded_at < timeoutSeconds 
            // Status ini berlaku untuk SEMUA device (tidak hardcoded untuk device 3,4,5)
            $isOnline = false;
            $lastUpdateTime = null;
            $secondsAgo = null;
            $statusMessage = 'DISCONNECTED';
            $statusColor = 'danger'; // merah
            
            if ($latestMonitoring) {
                $lastUpdateTime = $latestMonitoring->recorded_at;
                // PENTING: Gunakan DATABASE TIME, bukan local PHP time!
                // Hitung selisih: database_now - recorded_time
                $diff = $serverTime->diff($lastUpdateTime);
                $secondsAgo = ($diff->days * 86400) + ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                
                // Semua device menggunakan logika yang sama
                if ($secondsAgo <= $timeoutSeconds) {
                    $isOnline = true;
                    $statusMessage = 'ONLINE';
                    $statusColor = 'success'; // hijau
                } elseif ($secondsAgo <= 300) { // 5 menit
                    $isOnline = false;
                    $statusMessage = 'OFFLINE';
                    $statusColor = 'warning'; // kuning
                } else {
                    $isOnline = false;
                    $statusMessage = 'DISCONNECTED'; // Tidak ada data 5+ menit
                    $statusColor = 'danger'; // merah
                }
            }

            // Determine temperature status
            $tempStatus = 'safe';  // hijau
            if ($latestMonitoring) {
                if ($latestMonitoring->temperature >= 30) {
                    $tempStatus = 'warning'; // kuning
                }
                if ($latestMonitoring->temperature > 35) {
                    $tempStatus = 'danger'; // merah
                }
            }

            // Determine humidity status
            $humidityStatus = 'safe'; // biru
            if ($latestMonitoring) {
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
                // ESP Connection Status - PENTING: Berbasis timestamp, tidak hardcoded
                'esp_online' => $isOnline,
                'esp_status' => $statusMessage, // ONLINE, OFFLINE, DISCONNECTED
                'esp_status_color' => $statusColor,
                'seconds_ago' => $secondsAgo, // Untuk debugging & real-time display
                'last_update' => $lastUpdateTime ? $lastUpdateTime->toIso8601String() : null,
                // Overall monitoring status
                'monitoring_status' => $latestMonitoring ? $latestMonitoring->status : null,
            ];
        }

        return response()->json([
            'success' => true,
            'timestamp' => now()->toIso8601String(),
            'timeout_seconds' => $timeoutSeconds,
            'data' => $deviceId ? ($data[0] ?? null) : $data,
        ], 200);
    }

    /**
     * REAL-TIME METHOD: Get latest data untuk indicators
     * Usage: fetch('/api/monitoring/get-latest?device_id=1')
     * Response time: < 100ms
     * Update interval: 2 detik di frontend
     */
    public function getLatestSimple(Request $request)
    {
        $deviceId = $request->get('device_id');
        
        if (!$deviceId) {
            return response()->json(['success' => false, 'message' => 'device_id required'], 400);
        }
        
        $latest = Monitoring::where('device_id', $deviceId)
            ->latest('recorded_at')->first();
        
        if (!$latest) {
            return response()->json(['success' => false, 'message' => 'No data'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'temperature' => (float) $latest->temperature,
                'humidity' => (float) $latest->humidity,
                'status' => $latest->status,
                'recorded_at' => $latest->recorded_at->format('Y-m-d H:i:s'),
                'timestamp' => $latest->recorded_at->getTimestamp(),
            ]
        ], 200);
    }

    /**
     * REAL-TIME METHOD: Get chart data untuk live chart update
     * Usage: fetch('/api/monitoring/get-chart-data?device_id=1&timeframe=1_hour')
     * Response time: < 200ms
     * Update interval: 5 detik di frontend
     */
    public function getChartDataSimple(Request $request)
    {
        $deviceId = $request->get('device_id');
        $timeframe = $request->get('timeframe', '1_hour');
        
        if (!$deviceId) {
            return response()->json(['success' => false, 'message' => 'device_id required'], 400);
        }
        
        // Calculate date range
        $now = \Carbon\Carbon::now();
        $startDate = match($timeframe) {
            '10_min'  => $now->copy()->subMinutes(10),
            '30_min'  => $now->copy()->subMinutes(30),
            '1_hour'  => $now->copy()->subHour(),
            '6_hours' => $now->copy()->subHours(6),
            '12_hours' => $now->copy()->subHours(12),
            '1_day'   => $now->copy()->subDay(),
            default   => $now->copy()->subHour(),
        };
        
        // Query data
        $monitorings = Monitoring::where('device_id', $deviceId)
            ->whereBetween('recorded_at', [$startDate, $now])
            ->orderBy('recorded_at', 'ASC')
            ->get();
        
        // Format for chart
        $temperatures = [];
        $humidities = [];
        $timestamps = [];
        $dates = [];
        
        foreach ($monitorings as $m) {
            $temperatures[] = (float) $m->temperature;
            $humidities[] = (float) $m->humidity;
            $timestamps[] = $m->recorded_at->getTimestamp() * 1000;
            $dates[] = $m->recorded_at->format('H:i:s');
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'temperatures' => $temperatures,
                'humidities' => $humidities,
                'timestamps' => $timestamps,
                'dates' => $dates,
                'count' => count($temperatures),
                'timeframe' => $timeframe,
            ]
        ], 200);
    }

    /**
     * Get semua devices yang tersedia
     * Digunakan untuk auto-populate device selector di dropdown
     * Akan otomatis menambah device baru ketika created
     * 
     * GET /api/monitoring/devices
     * 
     * Response:
     * {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "device_id": "DEVICE_ABC123",
     *       "device_name": "Ruangan A1",
     *       "location": "Lantai 1"
     *     },
     *     ...
     *   ]
     * }
     */
    public function getAllDevices()
    {
        $devices = Device::all(['id', 'device_name', 'location', 'device_id']);
        
        return response()->json([
            'success' => true,
            'data' => $devices,
        ], 200);
    }
}
