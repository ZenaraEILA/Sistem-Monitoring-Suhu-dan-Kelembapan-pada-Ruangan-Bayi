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
}
