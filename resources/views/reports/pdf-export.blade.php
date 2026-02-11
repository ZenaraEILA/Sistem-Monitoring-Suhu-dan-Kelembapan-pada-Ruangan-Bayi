<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Monitoring Suhu dan Kelembapan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #0d6efd;
            padding: 20px 0;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 24px;
            color: #0d6efd;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            color: #666;
        }
        .report-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .info-item {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 4px;
            background: #f9f9f9;
        }
        .info-label {
            font-weight: bold;
            color: #0d6efd;
        }
        .info-value {
            margin-top: 3px;
        }
        .summary-section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background-color: #0d6efd;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }
        .summary-item {
            border: 1px solid #ddd;
            padding: 12px;
            border-radius: 4px;
            text-align: center;
        }
        .summary-item .label {
            font-size: 11px;
            color: #666;
            margin-bottom: 5px;
        }
        .summary-item .value {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
        }
        .chart-container {
            margin: 20px 0;
            text-align: center;
            page-break-inside: avoid;
        }
        .chart-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        table thead {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
        }
        table th {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .incidents-section,
        .notes-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .incident-item,
        .note-item {
            border-left: 4px solid #ff6b6b;
            padding: 10px;
            margin-bottom: 10px;
            background-color: #fff5f5;
            border-radius: 4px;
        }
        .note-item {
            border-left-color: #0d6efd;
            background-color: #f0f7ff;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        .status-aman {
            color: #28a745;
            font-weight: bold;
        }
        .status-tidak-aman {
            color: #dc3545;
            font-weight: bold;
        }
        .page-break {
            page-break-after: always;
        }
        .recommendation {
            font-size: 10px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <div class="header">
        <h1>📊 LAPORAN MONITORING SUHU DAN KELEMBAPAN RUANGAN BAYI</h1>
        <p>{{ $device->location }} - {{ $device->device_name }}</p>
    </div>

    <!-- REPORT INFO -->
    <div class="report-info">
        <div class="info-item">
            <div class="info-label">📅 Tipe Laporan</div>
            <div class="info-value">
                @if($type === 'daily') Laporan Harian
                @elseif($type === 'weekly') Laporan Mingguan
                @else Laporan Bulanan
                @endif
            </div>
        </div>
        <div class="info-item">
            <div class="info-label">📆 Periode</div>
            <div class="info-value">{{ $summary['period_start'] }} s/d {{ $summary['period_end'] }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">🏥 Ruangan</div>
            <div class="info-value">{{ $device->device_name }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">📍 Lokasi</div>
            <div class="info-value">{{ $device->location }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">⏱️ Dicetak Pada</div>
            <div class="info-value">{{ $generatedAt->format('d/m/Y H:i:s') }}</div>
        </div>
        <div class="info-item">
            <div class="info-label">👤 Dicetak Oleh</div>
            <div class="info-value">{{ $generatedBy }}</div>
        </div>
    </div>

    <!-- RINGKASAN PENTING -->
    <div class="summary-section">
        <div class="section-title">📈 RINGKASAN STATISTIK PENTING</div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Suhu -->
            <div>
                <h3 style="color: #0d6efd; margin-bottom: 10px; font-size: 14px;">🌡️ SUHU (°C)</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="label">Maksimal</div>
                        <div class="value">{{ $summary['max_temperature'] }}°</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Minimal</div>
                        <div class="value">{{ $summary['min_temperature'] }}°</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Rata-rata</div>
                        <div class="value">{{ $summary['avg_temperature'] }}°</div>
                    </div>
                </div>
            </div>

            <!-- Kelembapan -->
            <div>
                <h3 style="color: #0d6efd; margin-bottom: 10px; font-size: 14px;">💧 KELEMBAPAN (%)</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="label">Maksimal</div>
                        <div class="value">{{ $summary['max_humidity'] }}%</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Minimal</div>
                        <div class="value">{{ $summary['min_humidity'] }}%</div>
                    </div>
                    <div class="summary-item">
                        <div class="label">Rata-rata</div>
                        <div class="value">{{ $summary['avg_humidity'] }}%</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status & Performance -->
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; margin-top: 15px;">
            <div class="summary-item">
                <div class="label">Status Aman</div>
                <div class="value status-aman">{{ $summary['safe_count'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">Status Tidak Aman</div>
                <div class="value status-tidak-aman">{{ $summary['unsafe_count'] }}</div>
            </div>
            <div class="summary-item">
                <div class="label">% Kejadian Tidak Aman</div>
                <div class="value">{{ $summary['unsafe_percentage'] }}%</div>
            </div>
            <div class="summary-item">
                <div class="label">Waktu Respons Rata-rata</div>
                <div class="value">{{ $summary['avg_response_time'] }} min</div>
            </div>
        </div>
    </div>

    <!-- CHARTS -->
    @if(!empty($chartImage) && file_exists($chartImage))
    <div class="chart-container">
        <h3 style="color: #0d6efd; margin-bottom: 15px; font-size: 14px;">📊 Grafik Monitoring Suhu & Kelembapan</h3>
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($chartImage)) }}" alt="Grafik Monitoring">
    </div>
    @endif

    @if(!empty($statusChartImage) && file_exists($statusChartImage))
    <div class="chart-container">
        <h3 style="color: #0d6efd; margin-bottom: 15px; font-size: 14px;">📌 Distribusi Status</h3>
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($statusChartImage)) }}" alt="Status Chart" style="max-width: 400px;">
    </div>
    @endif

    <div class="page-break"></div>

    <!-- DATA DETAIL -->
    <div class="summary-section">
        <div class="section-title">📋 DATA DETAIL MONITORING ({{ $monitorings->count() }} RECORDS)</div>

        @if($monitorings->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Tanggal/Waktu</th>
                    <th>Suhu (°C)</th>
                    <th>Kelembapan (%)</th>
                    <th>Status</th>
                    <th>Rekomendasi</th>
                    <th>Tindakan Perawat</th>
                    <th>Waktu Respons</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monitorings as $monitoring)
                <tr>
                    <td>{{ $monitoring->recorded_at->format('d/m/Y H:i:s') }}</td>
                    <td style="text-align: center;">{{ round($monitoring->temperature, 2) }}</td>
                    <td style="text-align: center;">{{ round($monitoring->humidity, 2) }}</td>
                    <td style="text-align: center;">
                        @if($monitoring->status === 'Aman')
                            <span class="status-aman">✓ Aman</span>
                        @else
                            <span class="status-tidak-aman">✗ Tidak Aman</span>
                        @endif
                    </td>
                    <td class="recommendation">{{ $monitoring->recommendation ?? '-' }}</td>
                    <td>{{ $monitoring->action_note ?? '-' }}</td>
                    <td style="text-align: center;">
                        @if($monitoring->response_time_minutes)
                            {{ round($monitoring->response_time_minutes, 2) }} min
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align: center; color: #999; padding: 20px;">Tidak ada data monitoring untuk periode ini.</p>
        @endif
    </div>

    <!-- INCIDENT MARKERS -->
    @if($incidents->count() > 0)
    <div class="incidents-section">
        <div class="section-title">⚠️ KEJADIAN PENTING ({{ $incidents->count() }} EVENTS)</div>

        @foreach($incidents as $incident)
        <div class="incident-item">
            <strong>{{ $incident->created_at->format('d/m/Y H:i:s') }} - {{ $incident->incident_type }}</strong>
            <p style="margin-top: 5px; font-size: 12px;">{{ $incident->description ?? 'Tidak ada deskripsi' }}</p>
        </div>
        @endforeach
    </div>
    @endif

    <!-- DOCTOR NOTES -->
    @if($doctorNotes->count() > 0)
    <div class="notes-section">
        <div class="section-title">📝 CATATAN DOKTER</div>

        @foreach($doctorNotes as $note)
        <div class="note-item">
            <strong>{{ $note->note_date->format('d/m/Y') }}</strong>
            <p style="margin-top: 5px; font-size: 12px;">{{ $note->content }}</p>
        </div>
        @endforeach
    </div>
    @endif

    <!-- FOOTER -->
    <div class="footer">
        <p>📄 Dokumen ini adalah laporan resmi dan dapat digunakan untuk keperluan medis dan arsip ruangan.</p>
        <p>🔒 Data yang terkandung dalam laporan ini bersifat rahasia dan hanya untuk penggunaan internal rumah sakit.</p>
        <p>Generated by: Sistem Monitoring Suhu & Kelembapan v1.0</p>
    </div>
</body>
</html>
