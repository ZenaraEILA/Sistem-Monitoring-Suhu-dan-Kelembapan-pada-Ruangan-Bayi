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
     * Show monitoring charts.
     */
    public function chart(Request $request)
    {
        $devices = Device::all();
        $selectedDevice = $request->get('device_id', $devices->first()->id ?? null);
        $days = $request->get('days', 7);

        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $monitorings = Monitoring::where('device_id', $selectedDevice)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at')
            ->get();

        // Format data for chart
        $chartData = [
            'temperatures' => $monitorings->pluck('temperature')->toArray(),
            'humidities' => $monitorings->pluck('humidity')->toArray(),
            'dates' => $monitorings->pluck('recorded_at')->map(function ($date) {
                return $date->format('d-m-Y H:i');
            })->toArray(),
        ];

        return view('monitoring.chart', compact('devices', 'selectedDevice', 'days', 'chartData'));
    }

    /**
     * Show hourly trend for selected date
     */
    public function hourlyTrend(Request $request)
    {
        $devices = Device::all();
        $selectedDevice = $request->get('device_id', $devices->first()->id ?? null);
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));

        $hourlyData = Monitoring::getHourlyData($selectedDevice, Carbon::parse($date));

        $chartData = [
            'hours' => $hourlyData->pluck('hour')->map(function ($hour) {
                return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
            })->toArray(),
            'avg_temperatures' => $hourlyData->pluck('avg_temp')->toArray(),
            'max_temperatures' => $hourlyData->pluck('max_temp')->toArray(),
            'min_temperatures' => $hourlyData->pluck('min_temp')->toArray(),
            'avg_humidities' => $hourlyData->pluck('avg_humidity')->toArray(),
        ];

        return view('monitoring.hourly-trend', compact('devices', 'selectedDevice', 'date', 'chartData', 'hourlyData'));
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
