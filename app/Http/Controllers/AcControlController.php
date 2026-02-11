<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\AcControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcControlController extends Controller
{
    /**
     * Check if user has AC control permission
     */
    private function authorize()
    {
        $allowedRoles = ['admin', 'petugas'];
        if (!in_array(Auth::user()->role, $allowedRoles)) {
            abort(403, 'Unauthorized AC control access');
        }
    }

    /**
     * Increase AC temperature
     */
    public function increase(Request $request)
    {
        $this->authorize();

        $device = Device::findOrFail($request->device_id);

        $result = AcControlService::increaseTemperature($device, Auth::user());

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Decrease AC temperature
     */
    public function decrease(Request $request)
    {
        $this->authorize();

        $device = Device::findOrFail($request->device_id);

        $result = AcControlService::decreaseTemperature($device, Auth::user());

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Turn ON AC
     */
    public function turnOn(Request $request)
    {
        $this->authorize();

        $device = Device::findOrFail($request->device_id);

        $result = AcControlService::turnOn($device, Auth::user());

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Turn OFF AC
     */
    public function turnOff(Request $request)
    {
        $this->authorize();

        $device = Device::findOrFail($request->device_id);

        $result = AcControlService::turnOff($device, Auth::user());

        if ($result['success']) {
            return response()->json($result, 200);
        } else {
            return response()->json($result, 400);
        }
    }

    /**
     * Get AC status and logs
     */
    public function getStatus(Request $request)
    {
        $device = Device::findOrFail($request->device_id);

        if (!$device->ac_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'AC control tidak tersedia untuk device ini',
            ], 404);
        }

        $recommendation = AcControlService::getTemperatureRecommendation(
            $request->current_temperature ?? 25,
            $device
        );
        $summary = AcControlService::getControlSummary($device);

        return response()->json([
            'success' => true,
            'data' => [
                'ac_enabled' => $device->ac_enabled,
                'ac_set_point' => $device->ac_set_point,
                'ac_status' => $device->ac_status,
                'ac_min_temp' => $device->ac_min_temp,
                'ac_max_temp' => $device->ac_max_temp,
                'recommendation' => $recommendation,
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Get recent AC logs
     */
    public function getLogs(Request $request)
    {
        $device = Device::findOrFail($request->device_id);
        $limit = $request->limit ?? 10;

        $logs = AcControlService::getRecentLogs($device, $limit);

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}

