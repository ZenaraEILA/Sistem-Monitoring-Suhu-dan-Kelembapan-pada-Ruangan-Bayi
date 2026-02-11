@extends('layouts.main')

@section('title', 'Export Laporan - Sistem Monitoring')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3"><i class="fas fa-download"></i> Export Laporan</h1>
        <p class="text-muted">Buat dan download laporan monitoring dalam format CSV</p>
    </div>
</div>

<div class="row">
    <!-- Daily Report -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-day"></i> Laporan Harian</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Download data monitoring untuk satu hari</p>
                <form action="{{ route('reports.export-daily') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="device_id_daily" class="form-label">Pilih Ruangan</label>
                        <select name="device_id" id="device_id_daily" class="form-select" required>
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}">
                                    {{ $device->device_name }} - {{ $device->location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date_daily" class="form-label">Pilih Tanggal</label>
                        <input type="date" name="date" id="date_daily" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="format_daily" class="form-label">Format File</label>
                        <select name="format" id="format_daily" class="form-select" required>
                            <option value="excel">Excel (CSV)</option>
                            <option value="pdf" disabled>PDF (segera hadir)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-download"></i> Download Laporan Harian
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Weekly Report -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-week"></i> Laporan Mingguan</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Download data monitoring untuk 7 hari</p>
                <form action="{{ route('reports.export-weekly') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="device_id_weekly" class="form-label">Pilih Ruangan</label>
                        <select name="device_id" id="device_id_weekly" class="form-select" required>
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}">
                                    {{ $device->device_name }} - {{ $device->location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="start_date_weekly" class="form-label">Tanggal Mulai (Senin)</label>
                        <input type="date" name="start_date" id="start_date_weekly" class="form-control" value="{{ now()->startOfWeek()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="format_weekly" class="form-label">Format File</label>
                        <select name="format" id="format_weekly" class="form-select" required>
                            <option value="excel">Excel (CSV)</option>
                            <option value="pdf" disabled>PDF (segera hadir)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-download"></i> Download Laporan Mingguan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Monthly Report -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-calendar"></i> Laporan Bulanan</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Download data monitoring untuk satu bulan</p>
                <form action="{{ route('reports.export-monthly') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="device_id_monthly" class="form-label">Pilih Ruangan</label>
                        <select name="device_id" id="device_id_monthly" class="form-select" required>
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}">
                                    {{ $device->device_name }} - {{ $device->location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="month_monthly" class="form-label">Pilih Bulan</label>
                        <input type="month" name="month" id="month_monthly" class="form-control" value="{{ now()->format('Y-m') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="format_monthly" class="form-label">Format File</label>
                        <select name="format" id="format_monthly" class="form-select" required>
                            <option value="excel">Excel (CSV)</option>
                            <option value="pdf" disabled>PDF (segera hadir)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-download"></i> Download Laporan Bulanan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Information -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Laporan</h5>
            </div>
            <div class="card-body">
                <h6>Isi Laporan:</h6>
                <ul>
                    <li><strong>Ringkasan Statistik:</strong> Rata-rata, maksimal, dan minimal suhu dan kelembapan</li>
                    <li><strong>Data Detail:</strong> Waktu pencatatan, suhu, kelembapan, status, dan catatan tindakan</li>
                    <li><strong>Analisis:</strong> Total kondisi aman dan tidak aman</li>
                </ul>

                <h6 class="mt-3">Format File:</h6>
                <ul>
                    <li><strong>CSV (Excel):</strong> Kompatibel dengan Microsoft Excel, Google Sheets, dan aplikasi spreadsheet lainnya</li>
                    <li><strong>PDF:</strong> Format profesional untuk cetak (sedang dalam pengembangan)</li>
                </ul>

                <h6 class="mt-3">Kegunaan:</h6>
                <ul>
                    <li>Dokumentasi untuk audit rumah sakit</li>
                    <li>Analisis trend jangka panjang</li>
                    <li>Pelaporan ke asuransi kesehatan</li>
                    <li>Referensi untuk investigasi insiden</li>
                    <li>Evaluasi performa sistem AC dan ventilasi</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection
