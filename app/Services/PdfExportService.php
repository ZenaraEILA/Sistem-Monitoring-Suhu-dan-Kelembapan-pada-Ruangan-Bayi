<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DoctorNote;
use App\Models\IncidentMarker;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PdfExportService
{
    /**
     * Export monitoring data to PDF
     */
    public static function export(Device $device, Collection $monitorings, Carbon $startDate, Carbon $endDate, string $type, string $filename)
    {
        // Generate charts
        $chartImage = ChartService::generateMonitoringChart($monitorings);
        $statusChartImage = ChartService::generateStatusChart($monitorings);

        // Prepare summary
        $summary = self::generateSummary($device, $monitorings, $startDate, $endDate);

        // Get doctor notes
        $doctorNotes = DoctorNote::where('device_id', $device->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Get incidents through monitoring relationship
        $incidents = IncidentMarker::whereHas('monitoring', function ($query) use ($device) {
            $query->where('device_id', $device->id);
        })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // Prepare data for view
        $data = [
            'device' => $device,
            'summary' => $summary,
            'monitorings' => $monitorings,
            'doctorNotes' => $doctorNotes,
            'incidents' => $incidents,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'type' => $type,
            'chartImage' => $chartImage,
            'statusChartImage' => $statusChartImage,
            'generatedAt' => Carbon::now(),
            'generatedBy' => Auth::user()->name ?? 'System',
        ];

        // Generate PDF
        $pdf = Pdf::loadView('reports.pdf-export', $data)
            ->setPaper('a4')
            ->setOption('margin-bottom', 0)
            ->setOption('margin-top', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Generate summary statistics
     */
    private static function generateSummary(Device $device, Collection $monitorings, Carbon $startDate, Carbon $endDate): array
    {
        $safeCount = $monitorings->where('status', 'Aman')->count();
        $unsafeCount = $monitorings->where('status', 'Tidak Aman')->count();
        $avgResponseTime = $monitorings->whereNotNull('response_time_minutes')->avg('response_time_minutes');

        // Get alerts/incidents count
        $incidentsCount = IncidentMarker::whereHas('monitoring', function ($query) use ($device) {
            $query->where('device_id', $device->id);
        })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        return [
            'device_name' => $device->device_name,
            'location' => $device->location,
            'period_start' => $startDate->format('d/m/Y'),
            'period_end' => $endDate->format('d/m/Y'),
            'total_records' => $monitorings->count(),
            'max_temperature' => $monitorings->max('temperature'),
            'min_temperature' => $monitorings->min('temperature'),
            'avg_temperature' => round($monitorings->avg('temperature'), 2),
            'max_humidity' => $monitorings->max('humidity'),
            'min_humidity' => $monitorings->min('humidity'),
            'avg_humidity' => round($monitorings->avg('humidity'), 2),
            'safe_count' => $safeCount,
            'unsafe_count' => $unsafeCount,
            'unsafe_percentage' => $unsafeCount > 0 ? round(($unsafeCount / ($safeCount + $unsafeCount)) * 100, 2) : 0,
            'avg_response_time' => $avgResponseTime ? round($avgResponseTime, 2) : 0,
            'incidents_count' => $incidentsCount,
        ];
    }

    /**
     * Get HTML representation of chart
     */
    public static function getChartHtml($imagePath)
    {
        if (!file_exists($imagePath) || empty($imagePath)) {
            return '';
        }

        $base64 = base64_encode(file_get_contents($imagePath));
        return '<img src="data:image/png;base64,' . $base64 . '" style="width: 100%; max-width: 700px; height: auto;">';
    }
}
