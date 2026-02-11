<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DeviceStatus;
use App\Models\Monitoring;
use App\Models\ArchivedData;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdvancedFeatureController extends Controller
{
    /**
     * Get early warning patterns for a device
     */
    public function getEarlyWarningPatterns(Device $device)
    {
        $patterns = [];
        $last30Days = now()->subDays(30);

        // Analyze hourly patterns untuk last 30 hari
        $hourlyStats = Monitoring::where('device_id', $device->id)
            ->where('created_at', '>=', $last30Days)
            ->selectRaw('HOUR(created_at) as hour')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(CASE WHEN status = "Tidak Aman" THEN 1 ELSE 0 END) as unsafe_count')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        foreach ($hourlyStats as $stat) {
            $unsafePercentage = ($stat->unsafe_count / $stat->count) * 100;
            
            if ($unsafePercentage > 20) {
                $patterns[] = [
                    'hour' => $stat->hour,
                    'percentage' => round($unsafePercentage, 2),
                    'warning' => "Jam {$stat->hour}:00 - Sering terjadi kondisi tidak aman (" . round($unsafePercentage, 1) . "%)",
                    'suggestion' => $this->getHourlyWarningTips($stat->hour),
                ];
            }
        }

        $device->update([
            'early_warning_patterns' => $patterns,
            'last_stability_check' => now(),
        ]);

        return response()->json([
            'success' => true,
            'patterns' => $patterns,
            'count' => count($patterns),
        ]);
    }

    /**
     * Get hourly warning tips
     */
    private function getHourlyWarningTips($hour)
    {
        if ($hour >= 7 && $hour <= 10) {
            return "Periode besuk pagi - pastikan AC aktif dan ventilasi tertutup";
        } elseif ($hour >= 12 && $hour <= 14) {
            return "Periode siang - antisipasi masuknya cahaya matahari, kuatkan pendingin ruangan";
        } elseif ($hour >= 17 && $hour <= 19) {
            return "Periode besuk sore - tingkatkan pengawasan suhu, pastikan AC normal";
        } else {
            return "Lakukan inspeksi rutin pada peralatan AC dan ventilasi";
        }
    }

    /**
     * Get device status and offline notification
     */
    public function getDeviceStatus(Device $device)
    {
        DeviceStatus::checkDeviceStatus($device);

        $status = $device->deviceStatus;

        $notificationText = '';
        if ($status->isOffline()) {
            $notificationText = "⚠️ Device Offline - Tidak ada data {$status->offline_minutes} menit terakhir";
        } else {
            $notificationText = "✅ Device Online";
        }

        return response()->json([
            'success' => true,
            'status' => $status,
            'is_online' => $status->isOnline(),
            'is_offline' => $status->isOffline(),
            'notification' => $notificationText,
            'last_data_at' => $status->last_data_at,
        ]);
    }

    /**
     * Get room stability indicator
     */
    public function getRoomStability(Device $device, Request $request)
    {
        $hours = $request->input('hours', 24);
        $startDate = now()->subHours($hours);

        $readings = $device->monitorings()
            ->where('created_at', '>=', $startDate)
            ->orderBy('recorded_at')
            ->get();

        if ($readings->isEmpty()) {
            return response()->json([
                'success' => true,
                'stability_status' => 'unknown',
                'stability_score' => 0,
                'message' => 'Tidak ada data untuk analisis',
            ]);
        }

        // Calculate temperature & humidity variance
        $tempVariance = $readings->max('temperature') - $readings->min('temperature');
        $humidityVariance = $readings->max('humidity') - $readings->min('humidity');

        // Score based on variance (lower variance = more stable)
        $tempScore = max(0, 100 - ($tempVariance * 5)); // setiap 1°C = -5 poin
        $humidityScore = max(0, 100 - ($humidityVariance * 1.67)); // setiap 1% = -1.67 poin

        $stabilityScore = round(($tempScore + $humidityScore) / 2);
        $stabilityStatus = $stabilityScore >= 80 ? 'stable' : ($stabilityScore >= 60 ? 'moderate' : 'unstable');

        $device->update([
            'stability_status' => $stabilityStatus,
            'stability_score' => $stabilityScore,
            'last_stability_check' => now(),
        ]);

        return response()->json([
            'success' => true,
            'stability_status' => $stabilityStatus,
            'stability_score' => $stabilityScore,
            'temp_variance' => round($tempVariance, 2),
            'humidity_variance' => round($humidityVariance, 2),
            'analysis_period_hours' => $hours,
            'readings_count' => $readings->count(),
        ]);
    }

    /**
     * Get average response time for petugas
     */
    public function getResponseTimeStats(Device $device, Request $request)
    {
        $days = $request->input('days', 7);
        $startDate = now()->subDays($days);

        $records = Monitoring::where('device_id', $device->id)
            ->where('created_at', '>=', $startDate)
            ->where('status', 'Tidak Aman')
            ->whereNotNull('response_time_minutes')
            ->get();

        if ($records->isEmpty()) {
            return response()->json([
                'success' => true,
                'average_response_time' => null,
                'message' => 'Belum ada data respons petugas',
                'total_incidents' => 0,
            ]);
        }

        $avgResponseTime = round($records->avg('response_time_minutes'), 2);
        $maxResponseTime = $records->max('response_time_minutes');
        $minResponseTime = $records->min('response_time_minutes');

        // Log activity
        AuditLog::log('view', "Melihat statistik response time untuk device {$device->id}", 'Device', $device->id);

        return response()->json([
            'success' => true,
            'average_response_time_minutes' => $avgResponseTime,
            'max_response_time_minutes' => $maxResponseTime,
            'min_response_time_minutes' => $minResponseTime,
            'total_incidents' => $records->count(),
            'analysis_period_days' => $days,
        ]);
    }

    /**
     * Archive old data (>30 days)
     */
    public function archiveOldData()
    {
        try {
            ArchivedData::archiveOldData(30);

            AuditLog::log('create', 'Mengeksekusi archive data otomatis', 'ArchivedData', null);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diarsip',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengarsip data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get archived data for a device
     */
    public function getArchivedData(Device $device, Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = ArchivedData::where('device_id', $device->id);

        if ($startDate) {
            $query->where('archive_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('archive_date', '<=', $endDate);
        }

        $archived = $query->orderBy('archive_date', 'desc')
            ->get()
            ->map(function ($archive) {
                return [
                    'date' => $archive->archive_date,
                    'record_count' => $archive->record_count,
                    'summary' => $archive->getSummary(),
                    'archived_at' => $archive->archived_at,
                ];
            });

        return response()->json([
            'success' => true,
            'archived_data' => $archived,
            'count' => $archived->count(),
        ]);
    }
}
