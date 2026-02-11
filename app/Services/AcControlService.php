<?php

namespace App\Services;

use App\Models\AcLog;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AcControlService
{
    /**
     * Increase AC temperature
     */
    public static function increaseTemperature(Device $device, User $user): array
    {
        return self::controlAC($device, $user, 'increase');
    }

    /**
     * Decrease AC temperature
     */
    public static function decreaseTemperature(Device $device, User $user): array
    {
        return self::controlAC($device, $user, 'decrease');
    }

    /**
     * Turn ON AC
     */
    public static function turnOn(Device $device, User $user): array
    {
        return self::controlAC($device, $user, 'turn_on');
    }

    /**
     * Turn OFF AC
     */
    public static function turnOff(Device $device, User $user): array
    {
        return self::controlAC($device, $user, 'turn_off');
    }

    /**
     * Main control AC method
     */
    private static function controlAC(Device $device, User $user, string $action): array
    {
        if (!$device->ac_enabled) {
            return [
                'success' => false,
                'message' => 'AC control tidak diaktifkan untuk device ini',
            ];
        }

        // Validate action
        if (!in_array($action, ['increase', 'decrease', 'turn_on', 'turn_off'])) {
            return [
                'success' => false,
                'message' => 'Aksi tidak valid',
            ];
        }

        // Calculate new temperature for increase/decrease actions
        $newSetPoint = $device->ac_set_point;
        if ($action === 'increase') {
            $newSetPoint = min($device->ac_set_point + 1, $device->ac_max_temp);
        } elseif ($action === 'decrease') {
            $newSetPoint = max($device->ac_set_point - 1, $device->ac_min_temp);
        } elseif ($action === 'turn_on') {
            $device->ac_status = true;
        } elseif ($action === 'turn_off') {
            $device->ac_status = false;
        }

        // Send command to ESP8266/ESP32
        $espResponse = self::sendToESP($device, $action, $newSetPoint);

        if (!$espResponse['success']) {
            // Log failed action
            AcLog::create([
                'user_id' => $user->id,
                'device_id' => $device->id,
                'action' => $action,
                'ac_set_point' => $newSetPoint,
                'status' => 'failed',
            ]);

            return [
                'success' => false,
                'message' => 'Gagal menghubungi ESP8266: ' . $espResponse['message'],
            ];
        }

        // Update device
        if (in_array($action, ['increase', 'decrease'])) {
            $device->ac_set_point = $newSetPoint;
        }
        $device->save();

        // Log successful action
        AcLog::create([
            'user_id' => $user->id,
            'device_id' => $device->id,
            'action' => $action,
            'ac_set_point' => $newSetPoint,
            'status' => 'success',
        ]);

        $actionLabel = self::getActionLabel($action);

        return [
            'success' => true,
            'message' => "AC berhasil di-{$actionLabel}",
            'data' => [
                'ac_set_point' => $device->ac_set_point,
                'ac_status' => $device->ac_status,
            ],
        ];
    }

    /**
     * Send command to ESP8266/ESP32
     */
    private static function sendToESP(Device $device, string $action, float $temperature): array
    {
        if (!$device->ac_api_url) {
            return [
                'success' => false,
                'message' => 'ESP API URL tidak dikonfigurasi',
            ];
        }

        try {
            $payload = [
                'device_id' => $device->device_id,
                'action' => $action,
                'set_point' => $temperature,
                'timestamp' => Carbon::now()->toIso8601String(),
            ];

            // Menambahkan API key jika ada
            if ($device->ac_api_key) {
                $payload['api_key'] = $device->ac_api_key;
            }

            $response = Http::timeout(5)
                ->post($device->ac_api_url, $payload);

            if ($response->successful()) {
                return ['success' => true];
            } else {
                return [
                    'success' => false,
                    'message' => 'ESP responded with error: ' . $response->status(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get readable label for action
     */
    private static function getActionLabel(string $action): string
    {
        return match ($action) {
            'increase' => 'naikkan',
            'decrease' => 'turunkan',
            'turn_on' => 'nyalakan',
            'turn_off' => 'matikan',
            default => 'ubah',
        };
    }

    /**
     * Get AC control recommendation based on current temperature
     */
    public static function getTemperatureRecommendation(float $currentTemp, Device $device): ?array
    {
        if (!$device->ac_enabled) {
            return null;
        }

        if ($currentTemp > 30) {
            return [
                'action' => 'decrease',
                'message' => '🌡️ Suhu tinggi! Klik untuk turunkan AC',
                'class' => 'btn-danger',
            ];
        } elseif ($currentTemp < 15) {
            return [
                'action' => 'increase',
                'message' => '❄️ Suhu rendah! Klik untuk naikkan AC',
                'class' => 'btn-info',
            ];
        }

        return null;
    }

    /**
     * Get recent AC control logs
     */
    public static function getRecentLogs(Device $device, int $limit = 10): array
    {
        return $device->acLogs()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get AC control summary
     */
    public static function getControlSummary(Device $device): array
    {
        $today = now()->startOfDay();
        
        return [
            'today_actions' => $device->acLogs()
                ->whereDate('created_at', $today)
                ->count(),
            'today_success' => $device->acLogs()
                ->whereDate('created_at', $today)
                ->where('status', 'success')
                ->count(),
            'today_failed' => $device->acLogs()
                ->whereDate('created_at', $today)
                ->where('status', 'failed')
                ->count(),
            'last_action' => $device->acLogs()
                ->orderByDesc('created_at')
                ->first()?->created_at,
        ];
    }
}
