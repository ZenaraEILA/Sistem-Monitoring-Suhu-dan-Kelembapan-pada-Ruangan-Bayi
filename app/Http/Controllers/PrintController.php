<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Monitoring;
use App\Models\DoctorNote;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    /**
     * Print today's condition for a device
     */
    public function printTodayCondition(Device $device, Request $request)
    {
        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();

        $todayData = $device->monitorings()
            ->whereBetween('recorded_at', [$today, $tomorrow])
            ->orderBy('recorded_at')
            ->get();

        if ($todayData->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data untuk hari ini',
            ]);
        }

        // Calculate statistics
        $stats = [
            'avg_temperature' => round($todayData->avg('temperature'), 2),
            'max_temperature' => $todayData->max('temperature'),
            'min_temperature' => $todayData->min('temperature'),
            'avg_humidity' => round($todayData->avg('humidity'), 2),
            'max_humidity' => $todayData->max('humidity'),
            'min_humidity' => $todayData->min('humidity'),
            'unsafe_count' => $todayData->where('status', 'Tidak Aman')->count(),
            'safe_count' => $todayData->where('status', 'Aman')->count(),
            'total_readings' => $todayData->count(),
        ];

        $doctorNotes = DoctorNote::where('device_id', $device->id)
            ->where('note_date', $today)
            ->get();

        $htmlContent = $this->generatePrintHTML($device, $today, $stats, $todayData, $doctorNotes);

        return view('print.condition', [
            'device' => $device,
            'date' => $today,
            'stats' => $stats,
            'readings' => $todayData,
            'doctor_notes' => $doctorNotes,
            'html_content' => $htmlContent,
        ]);
    }

    /**
     * Generate HTML for printing
     */
    private function generatePrintHTML($device, $date, $stats, $readings, $notes)
    {
        $html = <<<HTML
        <html>
        <head>
            <title>Laporan Monitoring - {$device->device_name}</title>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
                .section { margin: 20px 0; }
                .section-title { font-weight: bold; font-size: 16px; background-color: #f0f0f0; padding: 10px; margin-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f0f0f0; }
                .stat-box { display: inline-block; margin: 10px; padding: 10px 15px; border: 1px solid #ddd; border-radius: 5px; }
                .stat-label { font-size: 12px; color: #666; }
                .stat-value { font-size: 18px; font-weight: bold; color: #333; }
                .safe { color: green; }
                .unsafe { color: red; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2>LAPORAN MONITORING SUHU & KELEMBAPAN BAYI</h2>
                <p><strong>Ruangan:</strong> {$device->device_name}</p>
                <p><strong>Tanggal:</strong> {$date->format('d/m/Y H:i')}</p>
            </div>

            <div class="section">
                <div class="section-title">RINGKASAN KONDISI HARI INI</div>
                <div class="stat-box">
                    <div class="stat-label">Rata-rata Suhu</div>
                    <div class="stat-value">{$stats['avg_temperature']}°C</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Range Suhu</div>
                    <div class="stat-value">{$stats['min_temperature']}°C - {$stats['max_temperature']}°C</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Rata-rata Kelembapan</div>
                    <div class="stat-value">{$stats['avg_humidity']}%</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Status Aman</div>
                    <div class="stat-value safe">{$stats['safe_count']} kali</div>
                </div>
                <div class="stat-box">
                    <div class="stat-label">Status Tidak Aman</div>
                    <div class="stat-value unsafe">{$stats['unsafe_count']} kali</div>
                </div>
            </div>

            <div class="section">
                <div class="section-title">DATA MONITORING</div>
                <table>
                    <tr>
                        <th>Waktu</th>
                        <th>Suhu (°C)</th>
                        <th>Kelembapan (%)</th>
                        <th>Status</th>
                    </tr>
HTML;

        foreach ($readings as $reading) {
            $statusClass = $reading->status === 'Aman' ? 'safe' : 'unsafe';
            $html .= <<<HTML
            <tr>
                <td>{$reading->recorded_at->format('H:i')}</td>
                <td>{$reading->temperature}</td>
                <td>{$reading->humidity}</td>
                <td class="{$statusClass}">{$reading->status}</td>
            </tr>
HTML;
        }

        $html .= '</table>';

        if (!$notes->isEmpty()) {
            $html .= '<div class="section"><div class="section-title">CATATAN DOKTER</div><ul>';
            foreach ($notes as $note) {
                $html .= "<li><strong>[{$note->category}]</strong> {$note->content}</li>";
            }
            $html .= '</ul></div>';
        }

        $html .= <<<HTML
            <div style="margin-top: 40px; text-align: right;">
                <p>Dicetak: {$date->now()->format('d/m/Y H:i:s')}</p>
            </div>
        </body>
        </html>
HTML;

        return $html;
    }

    /**
     * Download print as PDF
     */
    public function downloadPDF(Device $device, Request $request)
    {
        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();

        $todayData = $device->monitorings()
            ->whereBetween('recorded_at', [$today, $tomorrow])
            ->orderBy('recorded_at')
            ->get();

        // Stats calculation
        $stats = [
            'avg_temperature' => round($todayData->avg('temperature'), 2),
            'max_temperature' => $todayData->max('temperature'),
            'min_temperature' => $todayData->min('temperature'),
            'avg_humidity' => round($todayData->avg('humidity'), 2),
            'max_humidity' => $todayData->max('humidity'),
            'min_humidity' => $todayData->min('humidity'),
            'unsafe_count' => $todayData->where('status', 'Tidak Aman')->count(),
            'safe_count' => $todayData->where('status', 'Aman')->count(),
            'total_readings' => $todayData->count(),
        ];

        // Need PDF library - using simple HTML download for now
        return response()->json([
            'success' => true,
            'message' => 'PDF generation requires barryvdh/laravel-dompdf package',
            'device' => $device->device_name,
            'date' => $today,
            'stats' => $stats,
        ]);
    }
}
