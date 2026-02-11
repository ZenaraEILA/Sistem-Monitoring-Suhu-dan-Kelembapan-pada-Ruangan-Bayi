<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DoctorNote;
use App\Models\IncidentMarker;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelExportService
{
    /**
     * Export monitoring data to Excel
     */
    public static function export(Device $device, Collection $monitorings, Carbon $startDate, Carbon $endDate, string $type, string $filename)
    {
        // Prepare summary data
        $summary = self::generateSummary($device, $monitorings, $startDate, $endDate);

        // Prepare detailed data
        $detailedData = self::prepareDetailedData($monitorings);

        // Prepare doctor notes
        $doctorNotes = DoctorNote::where('device_id', $device->id)
            ->whereBetween('note_date', [$startDate, $endDate])
            ->get();

        // Prepare incident markers through monitoring relationship
        $incidents = IncidentMarker::whereHas('monitoring', function ($query) use ($device) {
            $query->where('device_id', $device->id);
        })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        return Excel::download(
            new ExcelExportData($device, $summary, $detailedData, $doctorNotes, $incidents, $startDate, $endDate, $type),
            $filename . '.xlsx'
        );
    }

    /**
     * Generate summary statistics
     */
    private static function generateSummary(Device $device, Collection $monitorings, Carbon $startDate, Carbon $endDate): array
    {
        $safeCount = $monitorings->where('status', 'Aman')->count();
        $unsafeCount = $monitorings->where('status', 'Tidak Aman')->count();
        $avgResponseTime = $monitorings->whereNotNull('response_time_minutes')->avg('response_time_minutes');

        return [
            'device_name' => $device->device_name,
            'location' => $device->location,
            'period' => $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
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
        ];
    }

    /**
     * Prepare detailed monitoring data
     */
    private static function prepareDetailedData(Collection $monitorings): array
    {
        $data = [];

        foreach ($monitorings as $monitoring) {
            $data[] = [
                $monitoring->recorded_at->format('d/m/Y H:i:s'),
                round($monitoring->temperature, 2),
                round($monitoring->humidity, 2),
                $monitoring->status,
                $monitoring->recommendation,
                $monitoring->action_note ?? '-',
                $monitoring->response_time_minutes ? round($monitoring->response_time_minutes, 2) . ' min' : '-',
            ];
        }

        return $data;
    }
}

/**
 * Excel export class for handling spreadsheet generation
 */
class ExcelExportData implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles
{
    private $device;
    private $summary;
    private $detailedData;
    private $doctorNotes;
    private $incidents;
    private $startDate;
    private $endDate;
    private $type;

    public function __construct($device, $summary, $detailedData, $doctorNotes, $incidents, $startDate, $endDate, $type)
    {
        $this->device = $device;
        $this->summary = $summary;
        $this->detailedData = $detailedData;
        $this->doctorNotes = $doctorNotes;
        $this->incidents = $incidents;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type;
    }

    public function array(): array
    {
        $data = [];

        // Title
        $data[] = ['LAPORAN MONITORING SUHU DAN KELEMBAPAN RUANGAN BAYI'];
        $data[] = [''];

        // Report Info
        $reportType = $this->type === 'daily' ? 'HARIAN' : ($this->type === 'weekly' ? 'MINGGUAN' : 'BULANAN');
        $data[] = ['Tipe Laporan:', $reportType];
        $data[] = ['Nama Ruangan:', $this->device->device_name];
        $data[] = ['Lokasi:', $this->device->location];
        $data[] = ['Periode:', $this->summary['period']];
        $data[] = ['Dicetak pada:', Carbon::now()->format('d/m/Y H:i:s')];
        $data[] = ['Dicetak oleh:', Auth::user()->name ?? 'System'];
        $data[] = [''];

        // Summary Section
        $data[] = ['RINGKASAN STATISTIK'];
        $data[] = ['Total Data Point:', $this->summary['total_records']];
        $data[] = [''];
        $data[] = ['SUHU (°C)'];
        $data[] = ['Maksimal:', $this->summary['max_temperature']];
        $data[] = ['Minimal:', $this->summary['min_temperature']];
        $data[] = ['Rata-rata:', $this->summary['avg_temperature']];
        $data[] = [''];
        $data[] = ['KELEMBAPAN (%)'];
        $data[] = ['Maksimal:', $this->summary['max_humidity']];
        $data[] = ['Minimal:', $this->summary['min_humidity']];
        $data[] = ['Rata-rata:', $this->summary['avg_humidity']];
        $data[] = [''];
        $data[] = ['STATUS MONITORING'];
        $data[] = ['Aman:', $this->summary['safe_count']];
        $data[] = ['Tidak Aman:', $this->summary['unsafe_count']];
        $data[] = ['Persentase Tidak Aman:', $this->summary['unsafe_percentage'] . '%'];
        $data[] = ['Rata-rata Waktu Respons:', $this->summary['avg_response_time'] . ' menit'];
        $data[] = [''];

        // Detailed data
        $data[] = ['DATA DETAIL MONITORING'];
        $data[] = ['Tanggal/Waktu', 'Suhu (°C)', 'Kelembapan (%)', 'Status', 'Rekomendasi', 'Tindakan', 'Waktu Respons'];

        foreach ($this->detailedData as $row) {
            $data[] = $row;
        }

        $data[] = [''];

        // Incident markers
        if ($this->incidents->count() > 0) {
            $data[] = ['KEJADIAN PENTING'];
            $data[] = ['Waktu', 'Tipe', 'Deskripsi'];

            foreach ($this->incidents as $incident) {
                $data[] = [
                    $incident->created_at->format('d/m/Y H:i:s'),
                    $incident->incident_type,
                    $incident->description ?? '-',
                ];
            }

            $data[] = [''];
        }

        // Doctor notes
        if ($this->doctorNotes->count() > 0) {
            $data[] = ['CATATAN DOKTER'];
            $data[] = ['Tanggal', 'Catatan'];

            foreach ($this->doctorNotes as $note) {
                $data[] = [
                    $note->note_date->format('d/m/Y'),
                    $note->content,
                ];
            }

            $data[] = [''];
        }

        // Footer
        $data[] = ['Catatan: Laporan ini adalah dokumen resmi dan dapat digunakan untuk arsip medis.'];

        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->getStyle('A1:D1')->getFont()->setSize(14)->setBold(true);
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(15);

        return [];
    }
}
