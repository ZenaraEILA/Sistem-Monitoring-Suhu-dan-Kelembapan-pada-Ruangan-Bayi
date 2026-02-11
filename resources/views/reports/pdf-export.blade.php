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
            color: #1a1a1a;
            background: white;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        /* ===== PAGE SETTINGS ===== */
        .page {
            width: 210mm;
            height: 297mm;
            margin: 0;
            padding: 0;
            page-break-after: always;
            position: relative;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        /* Page content wrapper */
        .page-content {
            width: 100%;
            height: 100%;
            padding: 30px 25px;
            position: relative;
        }

        /* ===== SLIDE 1: COVER PAGE ===== */
        .cover-page {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 50%, #0dcaf0 100%);
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .cover-page::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            z-index: 0;
        }

        .cover-page .page-content {
            padding: 50px 25px;
            text-align: center;
            height: auto;
            min-height: 297mm;
        }

        .cover-content {
            position: relative;
            z-index: 2;
            margin: 30px 0;
        }

        .cover-logo {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .cover-title {
            font-size: 36px;
            font-weight: 900;
            margin-bottom: 10px;
            letter-spacing: -0.8px;
        }

        .cover-subtitle {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 35px;
            opacity: 0.95;
        }

        .cover-info {
            background: rgba(255, 255, 255, 0.15);
            padding: 22px;
            border-radius: 8px;
            margin: 0 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }

        .info-row {
            margin: 7px 0;
            font-size: 10px;
            line-height: 1.4;
        }

        .info-label {
            font-weight: 700;
            display: inline-block;
            min-width: 110px;
            text-align: right;
            margin-right: 12px;
        }

        .info-value {
            font-weight: 500;
            display: inline-block;
            min-width: 200px;
            text-align: left;
        }

        .cover-footer {
            position: relative;
            font-size: 9px;
            opacity: 0.9;
            margin-top: 30px;
            z-index: 2;
        }

        /* ===== SLIDE 2: SUMMARY PAGE ===== */
        .page-title {
            font-size: 20px;
            font-weight: 900;
            color: #0d6efd;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #0d6efd;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 12px;
        }

        .summary-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 11px;
            position: relative;
            overflow: hidden;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(to right, #0d6efd, #0099ff, #0dcaf0);
        }

        .summary-card-label {
            font-size: 7px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .summary-card-value {
            font-size: 18px;
            font-weight: 900;
            color: #0d6efd;
            margin-bottom: 2px;
        }

        .summary-card-unit {
            font-size: 7px;
            color: #6c757d;
            font-weight: 600;
        }

        .status-stable {
            color: #198754;
            font-weight: 700;
        }

        .status-unstable {
            color: #dc3545;
            font-weight: 700;
        }

        /* ===== SLIDE 3: GRAFIK PAGE ===== */
        .chart-container-full {
            width: 100%;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9ff 100%);
            border: 1px solid #e0e7ff;
            border-radius: 6px;
            padding: 10px;
            margin-top: 10px;
        }

        .chart-description {
            font-size: 8px;
            color: #6c757d;
            margin-bottom: 8px;
            font-style: italic;
            text-align: center;
        }

        .chart-img {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 4px;
            max-height: 420px;
        }

        /* ===== SLIDE 4: DATA TABLE PAGE ===== */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 8px;
        }

        .data-table thead {
            background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%);
            color: white;
            font-weight: 800;
        }

        .data-table th {
            padding: 8px;
            text-align: left;
            border: 1px solid #0099ff;
            text-transform: uppercase;
            font-size: 6.5px;
            letter-spacing: 0.3px;
        }

        .data-table td {
            padding: 6px;
            border: 1px solid #e9ecef;
        }

        .data-table tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 5.5px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .badge-aman {
            background: linear-gradient(135deg, #d1f2eb 0%, #c3fae8 100%);
            color: #0f5132;
        }

        .badge-tidak-aman {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%);
            color: #842029;
        }

        /* ===== SLIDE 5: NOTES PAGE ===== */
        .notes-section {
            margin-bottom: 12px;
        }

        .notes-title {
            font-size: 11px;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1.5px solid #0d6efd;
        }

        .note-item {
            background: linear-gradient(to right, #f0f7ff 0%, #ffffff 100%);
            border-left: 2px solid #0d6efd;
            padding: 8px;
            margin-bottom: 6px;
            border-radius: 3px;
            border: 0.5px solid #d1e7ff;
            font-size: 8px;
        }

        .note-date {
            font-size: 7px;
            color: #0d6efd;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .note-content {
            font-size: 8px;
            color: #495057;
            line-height: 1.4;
        }

        .incident-item {
            background: linear-gradient(to right, #fff5f5 0%, #ffffff 100%);
            border-left: 2px solid #dc3545;
            padding: 8px;
            margin-bottom: 6px;
            border-radius: 3px;
            border: 0.5px solid #f8d7da;
            font-size: 8px;
        }

        .incident-time {
            font-size: 7px;
            color: #dc3545;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .empty-state {
            text-align: center;
            padding: 12px;
            color: #999;
            font-style: italic;
            font-size: 9px;
        }

        /* ===== SLIDE 6: CLOSING PAGE ===== */
        .closing-page {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            position: relative;
        }

        .closing-page::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 250px;
            height: 250px;
            background: radial-gradient(circle, rgba(13, 110, 253, 0.08) 0%, transparent 70%);
            z-index: 1;
        }

        .closing-page .page-content {
            padding: 50px 25px;
            text-align: center;
            height: auto;
            min-height: 297mm;
            position: relative;
            z-index: 2;
        }

        .closing-content {
            text-align: center;
            max-width: 500px;
            margin: 30px auto;
        }

        .closing-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .closing-title {
            font-size: 22px;
            font-weight: 900;
            color: #0d6efd;
            margin-bottom: 12px;
        }

        .closing-text {
            font-size: 9px;
            color: #495057;
            line-height: 1.6;
            margin-bottom: 18px;
        }

        .closing-divider {
            width: 60px;
            height: 2px;
            background: linear-gradient(to right, #0d6efd, #0099ff, #0dcaf0);
            margin: 18px auto;
            border-radius: 1px;
        }

        .closing-footer {
            font-size: 8px;
            color: #6c757d;
            margin-top: 18px;
        }

        /* Print styles */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .page {
                margin: 0;
                padding: 0;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <!-- ===== SLIDE 1: COVER PAGE ===== -->
    <div class="page cover-page">
        <div class="page-content">
            <div class="cover-content">
                <div class="cover-logo">📋</div>
                <div class="cover-title">LAPORAN MONITORING</div>
                <div class="cover-subtitle">Suhu & Kelembapan Ruang Bayi</div>

                <div class="cover-info">
                    <div class="info-row">
                        <div class="info-label">Ruangan:</div>
                        <div class="info-value">{{ $device->device_name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Lokasi:</div>
                        <div class="info-value">{{ $device->location ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Periode:</div>
                        <div class="info-value">{{ $summary['period_start'] ?? 'N/A' }} s/d {{ $summary['period_end'] ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Tipe Laporan:</div>
                        <div class="info-value">
                            @if($type === 'daily') Laporan Harian
                            @elseif($type === 'weekly') Laporan Mingguan
                            @else Laporan Bulanan
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dicetak Oleh:</div>
                        <div class="info-value">{{ $generatedBy ?? 'System' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Waktu Cetak:</div>
                        <div class="info-value">{{ $generatedAt->format('d/m/Y H:i:s') }}</div>
                    </div>
                </div>

                <div class="cover-footer">
                    <p>Dokumen ini adalah Laporan Resmi dari Sistem Monitoring Ruang Bayi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SLIDE 2: SUMMARY PAGE ===== -->
    <div class="page summary-page">
        <div class="page-content">
            <div class="page-title">📊 Ringkasan Penting</div>

            <div class="summary-grid">
                <div class="summary-card">
                    <div class="summary-card-label">Suhu Tertinggi</div>
                    <div class="summary-card-value">{{ $summary['max_temperature'] ?? 'N/A' }}</div>
                    <div class="summary-card-unit">°Celsius</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-label">Suhu Terendah</div>
                    <div class="summary-card-value">{{ $summary['min_temperature'] ?? 'N/A' }}</div>
                    <div class="summary-card-unit">°Celsius</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-label">Rata-rata Suhu</div>
                    <div class="summary-card-value">{{ $summary['avg_temperature'] ?? 'N/A' }}</div>
                    <div class="summary-card-unit">°Celsius</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-label">Kelembapan Tertinggi</div>
                    <div class="summary-card-value">{{ $summary['max_humidity'] ?? 'N/A' }}</div>
                    <div class="summary-card-unit">%</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-label">Kelembapan Terendah</div>
                    <div class="summary-card-value">{{ $summary['min_humidity'] ?? 'N/A' }}</div>
                    <div class="summary-card-unit">%</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-label">Rata-rata Kelembapan</div>
                    <div class="summary-card-value">{{ $summary['avg_humidity'] ?? 'N/A' }}</div>
                    <div class="summary-card-unit">%</div>
                </div>
            </div>

            <div class="summary-grid" style="margin-top: 20px;">
                <div class="summary-card">
                    <div class="summary-card-label">Status Aman</div>
                    <div class="summary-card-value" style="color: #198754;">{{ $summary['safe_count'] ?? 0 }}</div>
                    <div class="summary-card-unit">Kejadian</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-label">Status Tidak Aman</div>
                    <div class="summary-card-value" style="color: #dc3545;">{{ $summary['unsafe_count'] ?? 0 }}</div>
                    <div class="summary-card-unit">Kejadian</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-label">% Kejadian Tidak Aman</div>
                    <div class="summary-card-value">{{ $summary['unsafe_percentage'] ?? 0 }}</div>
                    <div class="summary-card-unit">%</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-label">Status Ruangan</div>
                    <div class="summary-card-value" style="font-size: 18px;">
                        @if(($summary['unsafe_percentage'] ?? 0) <= 5)
                        <span class="status-stable">✓ STABIL</span>
                        @else
                        <span class="status-unstable">⚠ TIDAK STABIL</span>
                        @endif
                    </div>
                    <div class="summary-card-unit">Kondisi</div>
                </div>
            </div>

            @if(isset($summary['avg_response_time']))
            <div class="summary-grid" style="margin-top: 20px;">
                <div class="summary-card">
                    <div class="summary-card-label">Rata-rata Waktu Respons</div>
                    <div class="summary-card-value">{{ $summary['avg_response_time'] }}</div>
                    <div class="summary-card-unit">Menit</div>
                </div>

                <div class="summary-card">
                    <div class="summary-card-label">Total Data Monitoring</div>
                    <div class="summary-card-value">{{ $monitorings->count() }}</div>
                    <div class="summary-card-unit">Records</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- ===== SLIDE 3: GRAFIK PAGE ===== -->
    @if(!empty($chartImage) && file_exists($chartImage))
    <div class="page chart-page">
        <div class="page-content">
            <div class="page-title">📈 Grafik Monitoring</div>

            <div class="chart-container-full">
                <div class="chart-description">
                    Dual Axis Chart — Axis Kiri: Suhu (°C) | Axis Kanan: Kelembapan (%)
                </div>
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($chartImage)) }}" alt="Chart" class="chart-img" style="max-height: 450px;">
            </div>
        </div>
    </div>
    @endif

    <!-- ===== SLIDE 4: DATA TABLE PAGE ===== -->
    @if($monitorings->count() > 0)
    <div class="page table-page">
        <div class="page-content">
            <div class="page-title">📋 Data Detail Monitoring ({{ $monitorings->count() }} Records)</div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal/Waktu</th>
                        <th>Suhu (°C)</th>
                        <th>Kelembapan (%)</th>
                        <th>Status</th>
                        <th>Tindakan Perawat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monitorings as $monitoring)
                    <tr>
                        <td>{{ $monitoring->recorded_at->format('d/m/Y H:i') }}</td>
                        <td style="text-align: center; font-weight: 600;">{{ round($monitoring->temperature, 2) }}</td>
                        <td style="text-align: center; font-weight: 600;">{{ round($monitoring->humidity, 2) }}</td>
                        <td style="text-align: center;">
                            @if($monitoring->status === 'Aman')
                            <span class="status-badge badge-aman">✓ Aman</span>
                            @else
                            <span class="status-badge badge-tidak-aman">✗ Tidak Aman</span>
                            @endif
                        </td>
                        <td>{{ substr($monitoring->action_note ?? '-', 0, 50) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- ===== SLIDE 5: CATATAN TAMBAHAN PAGE ===== -->
    @if($incidents->count() > 0 || $doctorNotes->count() > 0)
    <div class="page notes-page">
        <div class="page-content">
            <div class="page-title">📝 Catatan Tambahan</div>

            @if($incidents->count() > 0)
            <div class="notes-section">
                <div class="notes-title">⚠️ Incident Markers ({{ $incidents->count() }} Events)</div>
                @foreach($incidents as $incident)
                <div class="incident-item">
                    <div class="incident-time">{{ $incident->created_at->format('d/m/Y H:i:s') }} — {{ $incident->incident_type }}</div>
                    <div class="note-content">{{ $incident->description ?? 'Tidak ada deskripsi' }}</div>
                </div>
                @endforeach
            </div>
            @else
            <div class="notes-section">
                <div class="notes-title">⚠️ Incident Markers</div>
                <div class="empty-state">Tidak ada kejadian yang tercatat pada periode ini.</div>
            </div>
            @endif

            @if($doctorNotes->count() > 0)
            <div class="notes-section">
                <div class="notes-title">📋 Catatan Dokter ({{ $doctorNotes->count() }} Notes)</div>
                @foreach($doctorNotes as $note)
                <div class="note-item">
                    <div class="note-date">{{ $note->note_date->format('d/m/Y') }}</div>
                    <div class="note-content">{{ $note->content }}</div>
                </div>
                @endforeach
            </div>
            @else
            <div class="notes-section">
                <div class="notes-title">📋 Catatan Dokter</div>
                <div class="empty-state">Tidak ada catatan dokter pada periode ini.</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- ===== SLIDE 6: PENUTUP PAGE ===== -->
    <div class="page closing-page">
        <div class="page-content closing-content">
            <div class="closing-icon">✓</div>
            <div class="closing-title">Laporan Selesai</div>

            <div class="closing-text">
                <strong>Laporan ini adalah dokumen resmi yang dihasilkan secara otomatis oleh Sistem Monitoring Suhu & Kelembapan Ruang Bayi.</strong>
                <br><br>
                Dokumen ini telah diverifikasi dan siap untuk:
                <br>• Diarsipkan di rumah sakit
                <br>• Digunakan untuk evaluasi ruangan
                <br>• Dikomunikasikan kepada dokter penanggungjawab
                <br>• Keperluan medis dan dokumentasi
            </div>

            <div class="closing-divider"></div>

            <div class="closing-footer">
                <p><strong>Generated by:</strong> Sistem Monitoring Suhu & Kelembapan v1.0</p>
                <p><strong>Tanggal & Waktu:</strong> {{ $generatedAt->format('d/m/Y H:i:s') }}</p>
                <p><strong>User:</strong> {{ $generatedBy ?? 'System' }}</p>
                <br>
                <p style="margin-top: 20px; font-size: 8px; color: #999;">
                    © 2026 Sistem Monitoring Bayi - Rumah Sakit | Semua hak dilindungi
                </p>
            </div>
        </div>
    </div>

</body>
</html>
