<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DoctorNote;
use App\Models\IncidentMarker;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;

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
            'max_temperature' => $monitorings->max('temperature') ?? 0,
            'min_temperature' => $monitorings->min('temperature') ?? 0,
            'avg_temperature' => round($monitorings->avg('temperature') ?? 0, 2),
            'max_humidity' => $monitorings->max('humidity') ?? 0,
            'min_humidity' => $monitorings->min('humidity') ?? 0,
            'avg_humidity' => round($monitorings->avg('humidity') ?? 0, 2),
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
class ExcelExportData implements FromArray, WithStyles
{
    private $device;
    private $summary;
    private $detailedData;
    private $doctorNotes;
    private $incidents;
    private $startDate;
    private $endDate;
    private $type;

    private $rowCounter = 1;
    private $titleRow = 1;
    private $sectionHeaderRows = [];
    private $tableHeaderRows = [];
    private $dataRowRanges = [];

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
        $this->rowCounter = 1;

        // Title
        $data[] = ['LAPORAN MONITORING SUHU DAN KELEMBAPAN RUANGAN BAYI', '', '', '', '', '', ''];
        $this->titleRow = $this->rowCounter;
        $this->rowCounter++;
        $data[] = ['', '', '', '', '', '', ''];
        $this->rowCounter++;

        // Report Info
        $reportType = $this->type === 'daily' ? 'HARIAN' : ($this->type === 'weekly' ? 'MINGGUAN' : 'BULANAN');
        $data[] = ['Tipe Laporan:', $reportType, '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Nama Ruangan:', $this->device->device_name, '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Lokasi:', $this->device->location, '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Periode:', $this->summary['period'], '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Dicetak pada:', Carbon::now()->format('d/m/Y H:i:s'), '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Dicetak oleh:', Auth::user()->name ?? 'System', '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['', '', '', '', '', '', '']; $this->rowCounter++;

        // Summary Section
        $data[] = ['RINGKASAN STATISTIK', '', '', '', '', '', ''];
        $this->sectionHeaderRows[] = $this->rowCounter; $this->rowCounter++;
        
        $data[] = ['Total Data Point:', $this->summary['total_records'], '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['', '', '', '', '', '', '']; $this->rowCounter++;
        
        $data[] = ['SUHU (°C)', '', '', '', '', '', ''];
        $this->sectionHeaderRows[] = $this->rowCounter; $this->rowCounter++;
        
        $data[] = ['Maksimal:', $this->summary['max_temperature'], '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Minimal:', $this->summary['min_temperature'], '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Rata-rata:', $this->summary['avg_temperature'], '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['', '', '', '', '', '', '']; $this->rowCounter++;
        
        $data[] = ['KELEMBAPAN (%)', '', '', '', '', '', ''];
        $this->sectionHeaderRows[] = $this->rowCounter; $this->rowCounter++;
        
        $data[] = ['Maksimal:', $this->summary['max_humidity'], '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Minimal:', $this->summary['min_humidity'], '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Rata-rata:', $this->summary['avg_humidity'], '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['', '', '', '', '', '', '']; $this->rowCounter++;
        
        $data[] = ['STATUS MONITORING', '', '', '', '', '', ''];
        $this->sectionHeaderRows[] = $this->rowCounter; $this->rowCounter++;
        
        $data[] = ['Aman:', $this->summary['safe_count'], '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Tidak Aman:', $this->summary['unsafe_count'], '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Persentase Tidak Aman:', $this->summary['unsafe_percentage'] . '%', '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['Rata-rata Waktu Respons:', $this->summary['avg_response_time'] . ' menit', '', '', '', '', '']; $this->rowCounter++;
        $data[] = ['', '', '', '', '', '', '']; $this->rowCounter++;

        // Detailed data
        $data[] = ['DATA DETAIL MONITORING', '', '', '', '', '', ''];
        $this->sectionHeaderRows[] = $this->rowCounter; $this->rowCounter++;
        
        $data[] = ['Tanggal/Waktu', 'Suhu (°C)', 'Kelembapan (%)', 'Status', 'Rekomendasi', 'Tindakan', 'Waktu Respons'];
        $this->tableHeaderRows[] = $this->rowCounter; $this->rowCounter++;

        $startRow = $this->rowCounter;
        foreach ($this->detailedData as $row) {
            $data[] = $row;
            $this->rowCounter++;
        }
        $this->dataRowRanges[] = ['start' => $startRow, 'end' => $this->rowCounter - 1, 'endCol' => 'G'];

        $data[] = ['', '', '', '', '', '', '']; $this->rowCounter++;

        // Incident markers
        if ($this->incidents->count() > 0) {
            $data[] = ['KEJADIAN PENTING', '', '', '', '', '', ''];
            $this->sectionHeaderRows[] = $this->rowCounter; $this->rowCounter++;
            
            $data[] = ['Waktu', 'Tipe', 'Deskripsi', '', '', '', ''];
            $this->tableHeaderRows[] = $this->rowCounter; $this->rowCounter++;

            $startRow = $this->rowCounter;
            foreach ($this->incidents as $incident) {
                $data[] = [
                    $incident->created_at->format('d/m/Y H:i:s'),
                    $incident->incident_type,
                    $incident->description ?? '-',
                    '', '', '', ''
                ];
                $this->rowCounter++;
            }
            $this->dataRowRanges[] = ['start' => $startRow, 'end' => $this->rowCounter - 1, 'endCol' => 'C'];

            $data[] = ['', '', '', '', '', '', '']; $this->rowCounter++;
        }

        // Doctor notes
        if ($this->doctorNotes->count() > 0) {
            $data[] = ['CATATAN DOKTER', '', '', '', '', '', ''];
            $this->sectionHeaderRows[] = $this->rowCounter; $this->rowCounter++;
            
            $data[] = ['Tanggal', 'Catatan', '', '', '', '', ''];
            $this->tableHeaderRows[] = $this->rowCounter; $this->rowCounter++;

            $startRow = $this->rowCounter;
            foreach ($this->doctorNotes as $note) {
                $data[] = [
                    $note->note_date->format('d/m/Y'),
                    $note->content,
                    '', '', '', '', ''
                ];
                $this->rowCounter++;
            }
            $this->dataRowRanges[] = ['start' => $startRow, 'end' => $this->rowCounter - 1, 'endCol' => 'B'];

            $data[] = ['', '', '', '', '', '', '']; $this->rowCounter++;
        }

        // Footer
        $data[] = ['Catatan: Laporan ini adalah dokumen resmi dan dapat digunakan untuk arsip medis.', '', '', '', '', '', ''];

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(22);
        $sheet->getColumnDimension('B')->setWidth(18);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(18);

        // Title styling
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0D6EFD']
            ]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // General Info styling
        $sheet->getStyle('A3:A8')->getFont()->setBold(true);

        // Section Headers Styling
        foreach ($this->sectionHeaderRows as $row) {
            $sheet->getStyle("A$row")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => '0D6EFD'],
                ],
            ]);
        }

        // Table Headers Styling
        foreach ($this->tableHeaderRows as $row) {
            $sheet->getStyle("A$row:G$row")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0099FF']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '0077CC']
                    ],
                ]
            ]);
        }

        // Data Rows Styling
        foreach ($this->dataRowRanges as $range) {
            $start = $range['start'];
            $end = $range['end'];
            $endCol = $range['endCol'];
            if ($start <= $end) {
                // Borders
                $sheet->getStyle("A$start:$endCol$end")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => 'DDDDDD']
                        ],
                    ]
                ]);
                
                // Alignment (center for numeric/status columns)
                if ($endCol == 'G') {
                    $sheet->getStyle("B$start:D$end")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("G$start:G$end")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }
            }
        }

        return [];
    }
}
