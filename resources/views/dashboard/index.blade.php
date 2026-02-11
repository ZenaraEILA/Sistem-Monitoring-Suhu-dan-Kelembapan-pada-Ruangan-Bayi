@extends('layouts.main')

@section('title', 'Dashboard - Sistem Monitoring Suhu Bayi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0"><i class="fas fa-chart-line"></i> Dashboard Monitoring</h1>
    </div>
</div>

<!-- Emergency Alert (if exists) -->
@if(count($emergencyDevices) > 0)
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <h4 class="alert-heading"><i class="fas fa-exclamation-triangle"></i> ⚠️ KONDISI DARURAT!</h4>
    <p class="mb-2">Terdapat <strong>{{ count($emergencyDevices) }}</strong> ruangan dalam kondisi tidak normal selama lebih dari 5 menit:</p>
    <ul class="mb-0">
        @foreach($emergencyDevices as $device)
        <li>
            <strong>{{ $device->device_name }}</strong> ({{ $device->location }})
            @if(isset($emergencyDetails[$device->id]))
                <br><small>Suhu: {{ $emergencyDetails[$device->id]->temperature }}°C | Kelembapan: {{ $emergencyDetails[$device->id]->humidity }}% | Waktu: {{ $emergencyDetails[$device->id]->recorded_at->diffForHumans() }}</small>
            @endif
        </li>
        @endforeach
    </ul>
    <hr>
    <a href="{{ route('monitoring.emergency-incidents') }}" class="btn btn-sm btn-outline-danger">Lihat Semua Insiden Darurat</a>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Daily Summary -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="card-title mb-3"><i class="fas fa-calendar-day"></i> Ringkasan Hari Ini</h6>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="summary-item">
                            <div class="summary-value text-danger">{{ $todayUnsafeCount }}</div>
                            <div class="summary-label">Kejadian Tidak Normal</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-item">
                            <div class="summary-value text-success">{{ count($devices) }}</div>
                            <div class="summary-label">Total Ruangan Terpantau</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="summary-item">
                            <div class="summary-value text-info">{{ $safeCount }}</div>
                            <div class="summary-label">Ruangan dalam Kondisi Normal</div>
                        </div>
                    </div>
                </div>
                @if($todayUnsafeCount > 0)
                    <div class="alert alert-warning mt-3 mb-0">
                        <small><strong>Detail kejadian tidak normal:</strong></small><br>
                        @foreach($unsafeByDevice as $deviceName => $count)
                            <small>• {{ $deviceName }}: {{ $count }} kali</small><br>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-left-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Status Aman</h6>
                        <h2 class="mb-0">{{ $safeCount }}</h2>
                    </div>
                    <div class="text-success" style="font-size: 3rem; opacity: 0.3;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-left-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Status Tidak Aman</h6>
                        <h2 class="mb-0" style="color: #dc3545;">{{ $unsafeCount }}</h2>
                    </div>
                    <div class="text-danger" style="font-size: 3rem; opacity: 0.3;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Devices Monitoring -->
<div class="row">
    @forelse($devices as $device)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="device-card card h-100">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $device->device_name }}</h5>
                    @if($device->monitorings->count() > 0)
                        @php
                            $monitoring = $device->monitorings->first();
                            $statusBadge = $monitoring->status === 'Aman' ? 'status-safe' : 'status-unsafe';
                            $statusText = $monitoring->status;
                        @endphp
                        <span class="badge {{ $monitoring->status === 'Aman' ? 'badge-aman' : 'badge-tidak-aman' }}">
                            {{ $statusText }}
                        </span>
                    @endif
                </div>
                <small class="text-white-50"><i class="fas fa-map-marker-alt"></i> {{ $device->location }}</small>
            </div>
            <div class="card-body">
                @if($device->monitorings->count() > 0)
                    @php
                        $monitoring = $device->monitorings->first();
                    @endphp
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Suhu</small>
                            <div class="temp-display {{ $monitoring->temperature < 15 || $monitoring->temperature > 30 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($monitoring->temperature, 1) }}°C
                            </div>
                            <small class="text-success">✓ Normal: 15-30°C</small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Kelembapan</small>
                            <div class="humidity-display {{ $monitoring->humidity < 35 || $monitoring->humidity > 60 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($monitoring->humidity, 1) }}%
                            </div>
                            <small class="text-success">✓ Normal: 35-60%</small>
                        </div>
                    </div>
                    <hr>
                    
                    <!-- Recommendations -->
                    @php
                        $recommendations = $monitoring->recommendation_list;
                    @endphp
                    @if(count($recommendations) > 0)
                        <div class="alert alert-warning mb-3 py-2 px-3">
                            <small><strong>Rekomendasi Tindakan:</strong></small><br>
                            @foreach($recommendations as $rec)
                                <small>• {{ $rec }}</small><br>
                            @endforeach
                        </div>
                    @endif

                    <!-- Statistics for today -->
                    @if(isset($deviceStatistics[$device->id]))
                        <div class="device-stats mb-3">
                            <small class="text-muted d-block mb-2"><strong>Statistik Hari Ini:</strong></small>
                            <small class="d-block">
                                <i class="fas fa-thermometer-half"></i> 
                                Rata-rata: {{ $deviceStatistics[$device->id]['avg_temp'] }}°C | 
                                Max: {{ $deviceStatistics[$device->id]['max_temp'] }}°C | 
                                Min: {{ $deviceStatistics[$device->id]['min_temp'] }}°C
                            </small>
                            <small class="d-block">
                                <i class="fas fa-droplet"></i> 
                                Rata-rata: {{ $deviceStatistics[$device->id]['avg_humidity'] }}% | 
                                Max: {{ $deviceStatistics[$device->id]['max_humidity'] }}% | 
                                Min: {{ $deviceStatistics[$device->id]['min_humidity'] }}%
                            </small>
                            <small class="d-block text-danger mt-2">
                                ⚠️ Kondisi tidak normal: {{ $deviceStatistics[$device->id]['unsafe_count'] }} kali
                            </small>
                        </div>
                    @endif

                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> 
                        Terakhir diperbarui: {{ $monitoring->recorded_at->diffForHumans() }}
                    </small>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">Belum ada data monitoring</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info" role="alert">
            <i class="fas fa-info-circle"></i> Belum ada device terdaftar. 
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('device.create') }}">Tambahkan device sekarang</a>
            @endif
        </div>
    </div>
    @endforelse
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt"></i> Akses Cepat</h5>
            </div>
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="{{ route('monitoring.history') }}" class="btn btn-outline-primary">
                        <i class="fas fa-history"></i> Lihat Riwayat
                    </a>
                    <a href="{{ route('monitoring.chart') }}" class="btn btn-outline-primary">
                        <i class="fas fa-chart-area"></i> Lihat Grafik
                    </a>
                    <a href="{{ route('monitoring.hourly-trend') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-line"></i> Tren Harian
                    </a>
                    <a href="{{ route('report.index') }}" class="btn btn-outline-success">
                        <i class="fas fa-download"></i> Export Laporan
                    </a>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('device.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-microchip"></i> Kelola Device
                        </a>
                        <a href="{{ route('login-logs.index') }}" class="btn btn-outline-warning">
                            <i class="fas fa-sign-in-alt"></i> Riwayat Login
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Login Activity (for admin reference) -->
@if(auth()->user()->role === 'admin' && count($recentLoginLogs) > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sign-in-alt"></i> Aktivitas Login Terbaru</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Petugas</th>
                            <th>Waktu Login</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLoginLogs as $log)
                        <tr>
                            <td><strong>{{ $log->user->name }}</strong> ({{ $log->user->role }})</td>
                            <td>{{ $log->login_time->diffForHumans() }}</td>
                            <td><code>{{ $log->ip_address }}</code></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Tidak ada aktivitas login</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    <a href="{{ route('login-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                        Lihat Semua Riwayat Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<style>
    .border-left-primary {
        border-left: 4px solid var(--primary) !important;
    }

    .border-left-danger {
        border-left: 4px solid var(--danger) !important;
    }

    .device-card .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, #0056b3 100%);
        color: white;
    }

    .temp-display, .humidity-display {
        font-size: 1.8rem;
        font-weight: bold;
    }

    .summary-item {
        padding: 1rem;
    }

    .summary-value {
        font-size: 2rem;
        font-weight: bold;
        display: block;
    }

    .summary-label {
        color: #6c757d;
        font-size: 0.85rem;
    }

    .device-stats {
        background: #f8f9fa;
        padding: 0.75rem;
        border-radius: 0.25rem;
    }
</style>
@endsection
