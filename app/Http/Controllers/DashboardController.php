<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Monitoring;
use App\Models\LoginLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $devices = Device::with(['monitorings' => function ($query) {
            $query->latest('recorded_at')->limit(1);
        }])->get();

        // Get latest monitoring for each device - FIXED: Use whereIn instead of where
        $latestMonitorings = Monitoring::whereIn('id', function ($query) {
            $query->selectRaw('MAX(id)')
                ->from('monitorings')
                ->groupBy('device_id');
        })->get();

        // Count status
        $safeCount = $latestMonitorings->where('status', 'Aman')->count();
        $unsafeCount = $latestMonitorings->where('status', 'Tidak Aman')->count();

        // Check emergency conditions
        $emergencyDevices = [];
        $emergencyDetails = [];
        
        foreach ($devices as $device) {
            if (Monitoring::checkEmergencyCondition($device->id)) {
                $emergencyDevices[] = $device;
                $emergencyDetails[$device->id] = Monitoring::getLatestUnsafeDetails($device->id);
            }
        }

        // Get today's unsafe event count
        $todayUnsafeCount = 0;
        $unsafeByDevice = [];
        foreach ($devices as $device) {
            $count = Monitoring::countUnsafeToday($device->id);
            $todayUnsafeCount += $count;
            if ($count > 0) {
                $unsafeByDevice[$device->device_name] = $count;
            }
        }

        // Get today's date
        $today = Carbon::today();
        $date = request('date', $today->format('Y-m-d'));

        // Get summary statistics for each device
        $deviceStatistics = [];
        foreach ($devices as $device) {
            $deviceMonitorings = Monitoring::where('device_id', $device->id)
                ->whereDate('recorded_at', $date)
                ->get();

            if ($deviceMonitorings->count() > 0) {
                $deviceStatistics[$device->id] = [
                    'avg_temp' => round($deviceMonitorings->avg('temperature'), 2),
                    'max_temp' => $deviceMonitorings->max('temperature'),
                    'min_temp' => $deviceMonitorings->min('temperature'),
                    'avg_humidity' => round($deviceMonitorings->avg('humidity'), 2),
                    'max_humidity' => $deviceMonitorings->max('humidity'),
                    'min_humidity' => $deviceMonitorings->min('humidity'),
                    'unsafe_count' => $deviceMonitorings->where('status', 'Tidak Aman')->count(),
                ];
            }
        }

        // Get recent login logs for reference
        $recentLoginLogs = LoginLog::with('user')
            ->latest('login_time')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact(
            'devices',
            'latestMonitorings',
            'safeCount',
            'unsafeCount',
            'emergencyDevices',
            'emergencyDetails',
            'todayUnsafeCount',
            'unsafeByDevice',
            'date',
            'deviceStatistics',
            'recentLoginLogs'
        ));
    }
}
