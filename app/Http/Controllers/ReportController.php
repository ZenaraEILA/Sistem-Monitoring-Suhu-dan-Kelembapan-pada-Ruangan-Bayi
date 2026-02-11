<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Monitoring;
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
        return view('monitoring.report', compact('devices'));
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

        $filename = 'Laporan-' . $device->device_name . '-' . $date->format('Y-m-d');

        if ($validated['format'] === 'pdf') {
            return $this->generatePDF($device, $monitorings, $date, 'daily', $filename);
        } else {
            return $this->generateExcel($device, $monitorings, $date, 'daily', $filename);
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
            return $this->generatePDF($device, $monitorings, $startDate, 'weekly', $filename);
        } else {
            return $this->generateExcel($device, $monitorings, $startDate, 'weekly', $filename);
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
            return $this->generatePDF($device, $monitorings, $date, 'monthly', $filename);
        } else {
            return $this->generateExcel($device, $monitorings, $date, 'monthly', $filename);
        }
    }

    /**
     * Generate PDF report
     */
    private function generatePDF($device, $monitorings, $date, $type, $filename)
    {
        // Summary statistics
        $stats = [
            'total_records' => $monitorings->count(),
            'avg_temperature' => round($monitorings->avg('temperature'), 2),
            'max_temperature' => $monitorings->max('temperature'),
            'min_temperature' => $monitorings->min('temperature'),
            'avg_humidity' => round($monitorings->avg('humidity'), 2),
            'max_humidity' => $monitorings->max('humidity'),
            'min_humidity' => $monitorings->min('humidity'),
            'unsafe_count' => $monitorings->where('status', 'Tidak Aman')->count(),
            'safe_count' => $monitorings->where('status', 'Aman')->count(),
        ];

        // PDF generation would require barryvdh/laravel-dompdf
        // For now, return as JSON until PDF library is installed
        return response()->json([
            'message' => 'PDF export memerlukan instalasi laravel-dompdf',
            'device' => $device,
            'date' => $date->format('Y-m-d'),
            'type' => $type,
            'stats' => $stats,
            'total_data_points' => count($monitorings),
        ]);
    }

    /**
     * Generate Excel report
     */
    private function generateExcel($device, $monitorings, $date, $type, $filename)
    {
        // Excel generation would require phpoffice/phpspreadsheet or maatwebsite/excel
        // For now, return CSV as alternative
        $csv = "Device," . $device->device_name . "\n";
        $csv .= "Lokasi," . $device->location . "\n";
        $csv .= "Tipe Laporan," . ($type === 'daily' ? 'Harian' : ($type === 'weekly' ? 'Mingguan' : 'Bulanan')) . "\n";
        $csv .= "Tanggal Laporan," . $date->format('Y-m-d') . "\n";
        $csv .= "\n\n";

        // Summary
        $csv .= "RINGKASAN STATISTIK\n";
        $csv .= "Total Data Points," . $monitorings->count() . "\n";
        $csv .= "Rata-rata Suhu," . round($monitorings->avg('temperature'), 2) . "°C\n";
        $csv .= "Suhu Maksimal," . $monitorings->max('temperature') . "°C\n";
        $csv .= "Suhu Minimal," . $monitorings->min('temperature') . "°C\n";
        $csv .= "Rata-rata Kelembapan," . round($monitorings->avg('humidity'), 2) . "%\n";
        $csv .= "Kelembapan Maksimal," . $monitorings->max('humidity') . "%\n";
        $csv .= "Kelembapan Minimal," . $monitorings->min('humidity') . "%\n";
        $csv .= "Status Aman," . $monitorings->where('status', 'Aman')->count() . "\n";
        $csv .= "Status Tidak Aman," . $monitorings->where('status', 'Tidak Aman')->count() . "\n";
        $csv .= "\n\n";

        // Detailed data
        $csv .= "DATA DETAIL\n";
        $csv .= "Waktu Pencatatan,Suhu (°C),Kelembapan (%),Status,Catatan Tindakan\n";

        foreach ($monitorings as $monitoring) {
            $csv .= $monitoring->recorded_at->format('Y-m-d H:i:s') . ",";
            $csv .= $monitoring->temperature . ",";
            $csv .= $monitoring->humidity . ",";
            $csv .= $monitoring->status . ",";
            $csv .= ('"' . ($monitoring->action_note ?? '-') . '"') . "\n";
        }

        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');
    }
}
