<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Monitoring;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MonitoringController extends Controller
{
    /**
     * Show monitoring history.
     */
    public function history(Request $request)
    {
        $devices = Device::all();
        $selectedDevice = $request->get('device_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $startTime = $request->get('start_time');
        $endTime = $request->get('end_time');

        $query = Monitoring::with('device');

        if ($selectedDevice) {
            $query->where('device_id', $selectedDevice);
        }

        if ($startDate) {
            $startDateTime = Carbon::parse($startDate)->startOfDay();
            if ($startTime) {
                $startDateTime = Carbon::parse($startDate . ' ' . $startTime);
            }
            $query->where('recorded_at', '>=', $startDateTime);
        }

        if ($endDate) {
            $endDateTime = Carbon::parse($endDate)->endOfDay();
            if ($endTime) {
                $endDateTime = Carbon::parse($endDate . ' ' . $endTime);
            }
            $query->where('recorded_at', '<=', $endDateTime);
        }

        $monitorings = $query->latest('recorded_at')->paginate(50);

        return view('monitoring.history', compact('monitorings', 'devices', 'selectedDevice', 'startDate', 'endDate', 'startTime', 'endTime'));
    }

    /**
     * Show monitoring charts with quick timeframe presets
     */
    public function chart(Request $request)
    {
        $devices = Device::all();
        $selectedDevice = $request->get('device_id', $devices->first()->id ?? null);
        $timeframe = $request->get('timeframe', '1_day');

        // Calculate date range based on timeframe
        $now = Carbon::now();
        $startDate = match($timeframe) {
            '10_min' => $now->copy()->subMinutes(10),
            '30_min' => $now->copy()->subMinutes(30),
            '1_hour' => $now->copy()->subHour(),
            '6_hours' => $now->copy()->subHours(6),
            '12_hours' => $now->copy()->subHours(12),
            '1_day' => $now->copy()->subDay(),
            default => $now->copy()->subDay(),
        };

        $monitorings = Monitoring::where('device_id', $selectedDevice)
            ->whereBetween('recorded_at', [$startDate, $now])
            ->orderBy('recorded_at')
            ->get();

        // Format data for chart
        $temperatures = [];
        $humidities = [];
        $dates = [];
        $statuses = [];

        foreach ($monitorings as $monitoring) {
            $temperatures[] = $monitoring->temperature ?? 0;
            $humidities[] = $monitoring->humidity ?? 0;
            $dates[] = $monitoring->recorded_at->format('H:i:s');
            $statuses[] = $monitoring->status === 'Tidak Aman' ? 'danger' : 'safe';
        }

        $chartData = [
            'temperatures' => $temperatures,
            'humidities' => $humidities,
            'dates' => $dates,
            'statuses' => $statuses,
        ];

        return view('monitoring.chart', compact('devices', 'selectedDevice', 'timeframe', 'chartData'));
    }

    /**
     * Show hourly trend for selected date
     */
    public function hourlyTrend(Request $request)
    {
        $devices = Device::all();
        $selectedDevice = $request->get('device_id', $devices->first()->id ?? null);
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $startTime = $request->get('start_time', '00:00');
        $endTime = $request->get('end_time', '23:59');

        // Filter hourly data by time range
        $allHourlyData = Monitoring::getHourlyData($selectedDevice, Carbon::parse($date));
        
        // Convert time strings to hours
        $startHour = intval(explode(':', $startTime)[0]);
        $endHour = intval(explode(':', $endTime)[0]);
        
        // Filter by hour range
        $hourlyData = $allHourlyData->filter(function($item) use ($startHour, $endHour) {
            return $item->hour >= $startHour && $item->hour <= $endHour;
        })->values();

        $chartData = [
            'hours' => $hourlyData->pluck('hour')->map(function ($hour) {
                return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            })->toArray(),
            'avg_temperatures' => $hourlyData->pluck('avg_temp')->toArray(),
            'max_temperatures' => $hourlyData->pluck('max_temp')->toArray(),
            'min_temperatures' => $hourlyData->pluck('min_temp')->toArray(),
            'avg_humidities' => $hourlyData->pluck('avg_humidity')->toArray(),
        ];

        return view('monitoring.hourly-trend', compact('devices', 'selectedDevice', 'date', 'startTime', 'endTime', 'chartData', 'hourlyData'));
    }

    /**
     * Update action note for monitoring record
     */
    public function updateAction(Request $request, $id)
    {
        $validated = $request->validate([
            'action_note' => 'required|string|max:500',
        ]);

        $monitoring = Monitoring::findOrFail($id);
        $monitoring->update(['action_note' => $validated['action_note']]);

        return redirect()->back()->with('success', 'Catatan tindakan berhasil disimpan');
    }

    /**
     * Show emergency incidents
     */
    public function emergencyIncidents(Request $request)
    {
        $devices = Device::all();
        $selectedDevice = $request->get('device_id');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Monitoring::where('is_emergency', true);

        if ($selectedDevice) {
            $query->where('device_id', $selectedDevice);
        }

        if ($startDate) {
            $query->where('recorded_at', '>=', Carbon::parse($startDate)->startOfDay());
        }

        if ($endDate) {
            $query->where('recorded_at', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $emergencies = $query->with(['device'])
            ->latest('recorded_at')
            ->paginate(50);

        return view('monitoring.emergency-incidents', compact('emergencies', 'devices', 'selectedDevice', 'startDate', 'endDate'));
    }
}
