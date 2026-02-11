<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Monitoring;
use App\Services\PdfExportService;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Show report generation page
     */
    public function index(Request $request)
    {
        $devices = Device::all();
        return view('reports.index', compact('devices'));
    }

    /**
     * Export monitoring data for a device on a specific date
     */
    public function exportDaily(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'date' => 'required|date',
            'format' => 'required|in:pdf,excel',
        ]);

        $device = Device::findOrFail($validated['device_id']);
        $date = Carbon::parse($validated['date']);

        $monitorings = Monitoring::where('device_id', $device->id)
            ->whereDate('recorded_at', $date)
            ->orderBy('recorded_at')
            ->get();

        $filename = 'Laporan-Harian-' . $device->device_name . '-' . $date->format('Y-m-d');

        if ($validated['format'] === 'pdf') {
            return PdfExportService::export($device, $monitorings, $date, $date, 'daily', $filename);
        } else {
            return ExcelExportService::export($device, $monitorings, $date, $date, 'daily', $filename);
        }
    }

    /**
     * Export monitoring data for a device for a week
     */
    public function exportWeekly(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'start_date' => 'required|date',
            'format' => 'required|in:pdf,excel',
        ]);

        $device = Device::findOrFail($validated['device_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = $startDate->copy()->addDays(6);

        $monitorings = Monitoring::where('device_id', $device->id)
            ->whereBetween('recorded_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('recorded_at')
            ->get();

        $filename = 'Laporan-Mingguan-' . $device->device_name . '-' . $startDate->format('Y-m-d');

        if ($validated['format'] === 'pdf') {
            return PdfExportService::export($device, $monitorings, $startDate, $endDate, 'weekly', $filename);
        } else {
            return ExcelExportService::export($device, $monitorings, $startDate, $endDate, 'weekly', $filename);
        }
    }

    /**
     * Export monitoring data for a device for a month
     */
    public function exportMonthly(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'month' => 'required|date_format:Y-m',
            'format' => 'required|in:pdf,excel',
        ]);

        $device = Device::findOrFail($validated['device_id']);
        $date = Carbon::parse($validated['month'] . '-01');
        $startDate = $date->startOfMonth();
        $endDate = $date->endOfMonth();

        $monitorings = Monitoring::where('device_id', $device->id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at')
            ->get();

        $filename = 'Laporan-Bulanan-' . $device->device_name . '-' . $date->format('Y-m');

        if ($validated['format'] === 'pdf') {
            return PdfExportService::export($device, $monitorings, $startDate, $endDate, 'monthly', $filename);
        } else {
            return ExcelExportService::export($device, $monitorings, $startDate, $endDate, 'monthly', $filename);
        }
    }
}
