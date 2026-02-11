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
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.7;
            color: #1a1a1a;
            background: white;
            font-size: 10px;
        }
        
        /* ===== HEADER SECTION ===== */
        .header {
            text-align: center;
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 50%, #0dcaf0 100%);
            color: white;
            padding: 30px 25px;
            margin: -15px -15px 25px -15px;
            position: relative;
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
            clip-path: polygon(0 0, 100% 0, 100% calc(100% - 15px), 0 100%);
        }
        
        .header h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .header p {
            font-size: 12px;
            opacity: 0.95;
            margin: 0;
            font-weight: 500;
        }
        
        /* ===== REPORT INFO SECTION ===== */
        .report-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(to right, #f0f7ff 0%, #ffffff 100%);
            border-radius: 8px;
            border-left: 5px solid #0d6efd;
            position: relative;
        }
        
        .report-info::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100%;
            background: linear-gradient(to right, transparent 0%, rgba(13, 110, 253, 0.05) 100%);
            border-radius: 0 8px 8px 0;
        }
        
        .info-item {
            padding: 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e0e7ff;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        
        .info-item:hover {
            border-color: #0d6efd;
            box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
        }
        
        .info-label {
            font-size: 9px;
            color: #0d6efd;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.8px;
        }
        
        .info-value {
            font-size: 11px;
            color: #212529;
            font-weight: 600;
        }
        
        /* ===== SUMMARY SECTION ===== */
        .summary-section {
            margin-bottom: 28px;
            position: relative;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: 800;
            color: white;
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            padding: 14px 18px;
            margin-bottom: 18px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.25);
            clip-path: polygon(0 0, calc(100% - 20px) 0, 100% 100%, 0 100%);
            padding-right: 28px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 16px;
        }
        
        .summary-item {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e9ecef;
            padding: 16px;
            border-radius: 6px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .summary-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(to right, #0d6efd, #0099ff, #0dcaf0);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }
        
        .summary-item:hover {
            border-color: #0d6efd;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.15);
        }
        
        .summary-item:hover::before {
            transform: scaleX(1);
        }
        
        .summary-item .label {
            font-size: 9px;
            color: #6c757d;
            margin-bottom: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        
        .summary-item .value {
            font-size: 20px;
            font-weight: 800;
            color: #0d6efd;
            display: block;
        }
        
        /* ===== CHART SECTION ===== */
        .chart-container {
            margin: 22px 0;
            padding: 18px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border: 2px solid #e9ecef;
            border-radius: 8px;
            page-break-inside: avoid;
            position: relative;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }
        
        .chart-container::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(13, 110, 253, 0.03) 0%, transparent 70%);
            pointer-events: none;
        }
        
        .chart-container h3 {
            font-size: 12px;
            color: #0d6efd;
            margin-bottom: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            position: relative;
            z-index: 1;
        }
        
        .chart-container img {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 4px;
            position: relative;
            z-index: 1;
        }
        
        /* ===== TABLE STYLING ===== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
            font-size: 8.5px;
            page-break-inside: avoid;
        }
        
        table thead {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            color: white;
            font-weight: 800;
        }
        
        table th {
            padding: 12px;
            text-align: left;
            border: 2px solid #0099ff;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 8px;
        }
        
        table td {
            padding: 10px 12px;
            border: 1px solid #e9ecef;
            background: white;
        }
        
        table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }
        
        table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }
        
        table tbody tr:hover {
            background-color: #f0f7ff;
        }
        
        /* ===== STATUS BADGES ===== */
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .badge-aman {
            background: linear-gradient(135deg, #d1f2eb 0%, #c3fae8 100%);
            color: #0f5132;
            border: 1px solid #84e6d5;
        }
        
        .badge-tidak-aman {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%);
            color: #842029;
            border: 1px solid #f1b0b7;
        }
        
        .status-aman {
            color: #0f5132;
            font-weight: 700;
        }
        
        .status-tidak-aman {
            color: #842029;
            font-weight: 700;
        }
        
        /* ===== INCIDENTS SECTION ===== */
        .incidents-section {
            margin-top: 28px;
            margin-bottom: 28px;
            page-break-inside: avoid;
        }
        
        .incident-item {
            border-left: 5px solid #dc3545;
            padding: 14px;
            margin-bottom: 12px;
            background: linear-gradient(to right, #fff5f5 0%, #ffffff 100%);
            border-radius: 6px;
            border: 1px solid #f8d7da;
            position: relative;
        }
        
        .incident-item::before {
            content: '⚠️';
            position: absolute;
            top: 12px;
            right: 14px;
            font-size: 14px;
            opacity: 0.3;
        }
        
        .incident-item strong {
            color: #dc3545;
            font-size: 9px;
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .incident-item p {
            font-size: 9px;
            color: #495057;
            margin: 0;
            line-height: 1.5;
        }
        
        /* ===== NOTES SECTION ===== */
        .notes-section {
            margin-top: 28px;
            margin-bottom: 28px;
            page-break-inside: avoid;
        }
        
        .note-item {
            border-left: 5px solid #0d6efd;
            padding: 14px;
            margin-bottom: 12px;
            background: linear-gradient(to right, #f0f7ff 0%, #ffffff 100%);
            border-radius: 6px;
            border: 1px solid #d1e7ff;
            position: relative;
        }
        
        .note-item::before {
            content: '📝';
            position: absolute;
            top: 12px;
            right: 14px;
            font-size: 14px;
            opacity: 0.3;
        }
        
        .note-item strong {
            color: #0d6efd;
            font-size: 9px;
            display: block;
            margin-bottom: 6px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .note-item p {
            font-size: 9px;
            color: #495057;
            margin: 0;
            line-height: 1.5;
        }
        
        /* ===== FOOTER SECTION ===== */
        .footer {
            margin-top: 35px;
            border-top: 3px solid #0d6efd;
            padding-top: 16px;
            font-size: 8px;
            color: #6c757d;
            text-align: center;
            position: relative;
        }
        
        .footer::before {
            content: '';
            position: absolute;
            top: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(to right, #0d6efd, #0099ff, #0dcaf0);
            border-radius: 0 0 2px 0;
        }
        
        .footer p {
            margin: 4px 0;
            font-weight: 500;
        }
        
        /* ===== UTILITIES ===== */
        .page-break {
            page-break-after: always;
        }
        
        .recommendation {
            font-size: 8px;
            color: #6c757d;
            font-style: italic;
            font-weight: 500;
    </style>
</head>
<body>
    <!-- MAIN TITLE -->
    <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 4px solid #0d6efd; position: relative;">
        <h1 style="font-size: 32px; font-weight: 900; color: #0d6efd; margin: 0 0 10px 0; letter-spacing: -1px;">📊 LAPORAN MONITORING</h1>
        <h2 style="font-size: 14px; color: #0099ff; margin: 0 0 8px 0; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;">Suhu & Kelembapan Ruangan Bayi</h2>
        <p style="font-size: 11px; color: #6c757d; margin: 0; font-weight: 500;">{{ $device->location }} • {{ $device->device_name }}</p>
        <div style="position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%); width: 80px; height: 4px; background: linear-gradient(to right, #0d6efd, #0099ff, #0dcaf0); border-radius: 0 0 2px 2px;"></div>
    </div>

    <!-- HEADER -->
    <div class="header">
        <h1>📋 Informasi Laporan</h1>
        <p>Periode: {{ $summary['period_start'] ?? 'N/A' }} s/d {{ $summary['period_end'] ?? 'N/A' }}</p>
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
        <h3>📊 Grafik Dual Axis - Monitoring Suhu & Kelembapan</h3>
        <p style="font-size: 8px; color: #6c757d; margin: 0 0 12px 0; font-style: italic;">Axis Kiri: Suhu (°C) | Axis Kanan: Kelembapan (%)</p>
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($chartImage)) }}" alt="Grafik Monitoring">
    </div>
    @endif

    @if(!empty($statusChartImage) && file_exists($statusChartImage))
    <div class="chart-container">
        <h3>📌 Distribusi Status Monitoring</h3>
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($statusChartImage)) }}" alt="Status Chart" style="max-width: 350px; margin: 0 auto; display: block;">
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
                    <td style="text-align: center; font-weight: 600;">{{ round($monitoring->temperature, 2) }}</td>
                    <td style="text-align: center; font-weight: 600;">{{ round($monitoring->humidity, 2) }}</td>
                    <td style="text-align: center;">
                        @if($monitoring->status === 'Aman')
                            <span class="status-badge badge-aman">✓ Aman</span>
                        @else
                            <span class="status-badge badge-tidak-aman">✗ Tidak Aman</span>
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
