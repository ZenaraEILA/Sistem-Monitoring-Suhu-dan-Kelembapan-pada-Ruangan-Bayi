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
}
