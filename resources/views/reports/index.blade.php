@extends('layouts.main')

@section('title', 'Export Laporan - Sistem Monitoring Suhu Bayi')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3">📊 Export Laporan Monitoring</h1>
            <p class="text-muted">Unduh laporan monitoring dalam format PDF atau Excel untuk keperluan dokumentasi medis</p>
        </div>
    </div>

    <!-- Alert -->
    @if($devices->isEmpty())
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>⚠️ Perhatian!</strong> Belum ada device yang terdaftar. Silakan tambahkan device terlebih dahulu.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <!-- LAPORAN HARIAN -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📅 Laporan Harian</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.export-daily') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="daily_device_id" class="form-label">Pilih Ruangan/Device</label>
                            <select class="form-select @error('device_id') is-invalid @enderror" id="daily_device_id" name="device_id" required>
                                <option value="">-- Pilih Device --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->device_name }} ({{ $device->location }})</option>
                                @endforeach
                            </select>
                            @error('device_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="daily_date" class="form-label">Pilih Tanggal</label>
                            <input type="date" class="form-control @error('date') is-invalid @enderror" id="daily_date" name="date" 
                                   value="{{ old('date', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                            @error('date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Format Unduhan</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="format" id="daily_pdf" value="pdf" checked>
                                <label class="btn btn-outline-danger w-50" for="daily_pdf">
                                    📄 PDF
                                </label>

                                <input type="radio" class="btn-check" name="format" id="daily_excel" value="excel">
                                <label class="btn btn-outline-success w-50" for="daily_excel">
                                    📊 Excel
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-download"></i> Unduh Laporan
                        </button>
                    </form>

                    <div class="small text-muted mt-3">
                        <p class="mb-1">✓ Termasuk: Grafik, statistik, dan tabel data</p>
                        <p class="mb-1">✓ Cocok untuk: Laporan harian ke dokter</p>
                        <p class="mb-0">✓ Ukuran file: << 5 MB</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- LAPORAN MINGGUAN -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">📆 Laporan Mingguan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.export-weekly') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="weekly_device_id" class="form-label">Pilih Ruangan/Device</label>
                            <select class="form-select @error('device_id') is-invalid @enderror" id="weekly_device_id" name="device_id" required>
                                <option value="">-- Pilih Device --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->device_name }} ({{ $device->location }})</option>
                                @endforeach
                            </select>
                            @error('device_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="weekly_start_date" class="form-label">Pilih Hari Pertama Minggu</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="weekly_start_date" 
                                   name="start_date" value="{{ old('start_date', date('Y-m-d', strtotime('Monday this week'))) }}" 
                                   max="{{ date('Y-m-d') }}" required>
                            @error('start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Laporan untuk 7 hari mulai dari tanggal ini</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Format Unduhan</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="format" id="weekly_pdf" value="pdf" checked>
                                <label class="btn btn-outline-danger w-50" for="weekly_pdf">
                                    📄 PDF
                                </label>

                                <input type="radio" class="btn-check" name="format" id="weekly_excel" value="excel">
                                <label class="btn btn-outline-success w-50" for="weekly_excel">
                                    📊 Excel
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-info w-100">
                            <i class="bi bi-download"></i> Unduh Laporan
                        </button>
                    </form>

                    <div class="small text-muted mt-3">
                        <p class="mb-1">✓ Cakupan: 7 hari laporan</p>
                        <p class="mb-1">✓ Cocok untuk: Review mingguan</p>
                        <p class="mb-0">✓ Data points: ~2000++ records</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- LAPORAN BULANAN -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">📊 Laporan Bulanan</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.export-monthly') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="monthly_device_id" class="form-label">Pilih Ruangan/Device</label>
                            <select class="form-select @error('device_id') is-invalid @enderror" id="monthly_device_id" name="device_id" required>
                                <option value="">-- Pilih Device --</option>
                                @foreach($devices as $device)
                                    <option value="{{ $device->id }}">{{ $device->device_name }} ({{ $device->location }})</option>
                                @endforeach
                            </select>
                            @error('device_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="monthly_month" class="form-label">Pilih Bulan & Tahun</label>
                            <input type="month" class="form-control @error('month') is-invalid @enderror" id="monthly_month" 
                                   name="month" value="{{ old('month', date('Y-m')) }}" required>
                            @error('month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Format Unduhan</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="format" id="monthly_pdf" value="pdf" checked>
                                <label class="btn btn-outline-danger w-50" for="monthly_pdf">
                                    📄 PDF
                                </label>

                                <input type="radio" class="btn-check" name="format" id="monthly_excel" value="excel">
                                <label class="btn btn-outline-success w-50" for="monthly_excel">
                                    📊 Excel
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-download"></i> Unduh Laporan
                        </button>
                    </form>

                    <div class="small text-muted mt-3">
                        <p class="mb-1">✓ Cakupan: Seluruh bulan</p>
                        <p class="mb-1">✓ Cocok untuk: Laporan arsip & audit</p>
                        <p class="mb-0">✓ Data points: ~50.000++ records</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row mt-5">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">ℹ️ Informasi Laporan</h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-3">Apa yang Termasuk dalam Laporan?</h6>
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item">
                            <strong>📈 Ringkasan Statistik:</strong> Suhu/Kelembapan min, max, rata-rata, dan status monitoring
                        </li>
                        <li class="list-group-item">
                            <strong>📊 Grafik Visual:</strong> Chart interaktif yang menampilkan tren suhu dan kelembapan
                        </li>
                        <li class="list-group-item">
                            <strong>📋 Tabel Data Lengkap:</strong> Setiap data point dengan timestamp, status, dan tindakan
                        </li>
                        <li class="list-group-item">
                            <strong>⚠️ Kejadian Penting:</strong> Semua incident markers selama periode laporan
                        </li>
                        <li class="list-group-item">
                            <strong>📝 Catatan Dokter:</strong> Medical notes yang relevan dengan periode laporan
                        </li>
                        <li class="list-group-item">
                            <strong>🏥 Informasi Ruangan:</strong> Lokasi, nama device, nama petugas yang cetak laporan
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">💡 Tips Penggunaan</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>📄 PDF:</strong> Gunakan format PDF untuk laporan profesional yang siap dicetak
                        </li>
                        <li class="mb-2">
                            <strong>📊 Excel:</strong> Gunakan Excel untuk analisis data lebih lanjut dan pivot table
                        </li>
                        <li class="mb-2">
                            <strong>🖨️ Cetak:</strong> Laporan PDF bisa langsung dicetak dengan format rapi
                        </li>
                        <li class="mb-2">
                            <strong>📧 Email:</strong> Kirim laporan ke dokter atau pihak yang berwenang
                        </li>
                        <li class="mb-0">
                            <strong>🗂️ Arsip:</strong> Simpan laporan sebagai dokumen resmi ruangan
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .container-fluid {
        background-color: #f8f9fa;
    }
    .card {
        border: none;
        transition: box-shadow 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .card-header {
        border-bottom: none;
        font-weight: 600;
    }
    .btn-group label {
        padding: 10px;
        font-weight: 500;
    }
    .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    .list-group-item {
        border-left: 4px solid #0d6efd;
        font-size: 14px;
    }
</style>
@endsection
