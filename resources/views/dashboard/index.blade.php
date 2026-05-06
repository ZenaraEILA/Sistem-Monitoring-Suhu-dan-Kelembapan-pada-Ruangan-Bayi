@extends('layouts.main')

@section('title', 'Dashboard - Sistem Monitoring Suhu Bayi')

@section('content')

<!-- Realtime Connection Status Alert -->
<div id="connectionStatusAlert" class="alert alert-info alert-dismissible fade show d-flex justify-content-between align-items-center" role="alert" style="display: none;">
    <div>
        <span id="connectionStatusText">
            <i class="fas fa-wifi me-2"></i>
            <span id="connectionStatusMessage">Checking connection...</span>
        </span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Global connection alerts are handled in layouts/main.blade.php -->

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
            <h1 class="h3 mb-0"><i class="fas fa-chart-line"></i> Dashboard Monitoring</h1>
            <small class="text-muted">
                <i class="fas fa-sync-alt" id="refreshSpinner"></i> 
                Update terakhir: <span id="lastUpdateTime">sekarang</span>
            </small>
        </div>
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

<!-- Devices Monitoring -->
<div class="row" id="devicesContainer">
    @forelse($devices as $device)
    <div class="col-12 mb-4 device-monitor" data-device-id="{{ $device->id }}">
        <div class="row">
            <!-- Kolom Kiri: Kartu Status Ruangan -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <div class="device-card card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header border-0 py-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-door-closed me-2 text-white-50"></i>{{ $device->device_name }}</h5>
                    <span class="badge rounded-pill device-status-badge {{ $device->monitorings->count() > 0 && $device->monitorings->first()->status === 'Aman' ? 'badge-aman' : 'badge-tidak-aman' }} border border-light">
                        <span class="device-status-text">{{ $device->monitorings->count() > 0 ? $device->monitorings->first()->status : 'No Data' }}</span>
                    </span>
                </div>
                <small class="text-white-50"><i class="fas fa-map-marker-alt"></i> {{ $device->location }}</small>
            </div>

            <div class="card-body pt-3 pb-4 px-4">
                <!-- Connection Status Indicator -->
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #f8f9fa;">
                            <i class="fas fa-wifi device-connection-icon" style="color: #9ca3af;"></i>
                        </div>
                        <span class="live-dot disconnected"></span>
                         <span class="device-connection-status fw-bold text-dark" style="font-size: 0.85rem;">TIDAK TERHUBUNG</span>
                    </div>
                    <small class="text-muted device-last-update" style="font-size: 0.75rem;">
                        loading...
                    </small>
                </div>

                @if($device->monitorings->count() > 0)
                    @php
                        $monitoring = $device->monitorings->first();
                        
                        // Check if device is connected
                        $is_connected = false;
                        if ($monitoring) {
                            $dbNow = \DB::selectOne('SELECT NOW() as db_time');
                            $serverTime = new \DateTime($dbNow->db_time);
                            $diff = $serverTime->diff($monitoring->recorded_at);
                            $secondsAgo = ($diff->days * 86400) + ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
                            $is_connected = $secondsAgo <= 15;
                        }
                    @endphp
                    <div class="row text-center mb-4">
                        <div class="col-6 border-end">
                            <div class="text-muted mb-1" style="font-size: 0.85rem;"><i class="fas fa-temperature-half me-1"></i>Suhu</div>
                            <div class="temp-display device-temperature">
                                <span id="temp-val-{{ $device->id }}" class="device-temp-value fw-bold" style="font-size:2.2rem; color:{{ $monitoring->temperature < 15 || $monitoring->temperature > 30 ? '#dc3545' : '#198754' }};">{{ $is_connected ? number_format($monitoring->temperature, 1) : '0' }}</span><span class="fs-5" style="color:{{ $monitoring->temperature < 15 || $monitoring->temperature > 30 ? '#dc3545' : '#198754' }};">°C</span>
                            </div>
                            <div class="mt-1">
                                <span class="badge bg-light text-secondary border"><i class="fas fa-info-circle"></i> 15-30°C</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted mb-1" style="font-size: 0.85rem;"><i class="fas fa-droplet me-1"></i>Kelembapan</div>
                            <div class="humidity-display device-humidity">
                                <span id="hum-val-{{ $device->id }}" class="device-humidity-value fw-bold" style="font-size:2.2rem; color:{{ $monitoring->humidity < 35 || $monitoring->humidity > 60 ? '#dc3545' : '#198754' }};">{{ $is_connected ? number_format($monitoring->humidity, 1) : '0' }}</span><span class="fs-5" style="color:{{ $monitoring->humidity < 35 || $monitoring->humidity > 60 ? '#dc3545' : '#198754' }};">%</span>
                            </div>
                            <div class="mt-1">
                                <span class="badge bg-light text-secondary border"><i class="fas fa-info-circle"></i> 35-60%</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hardware Status -->
                    @php
                        $temp = $monitoring ? $monitoring->temperature : 25;
                        
                        $fan1_on = $is_connected && $temp >= 28;
                        $fan2_on = $is_connected && $temp > 30;
                        $heater_on = $is_connected && $temp < 28;
                    @endphp
                    <div class="row text-center mb-4 border-top pt-3 mx-0">
                        <div class="col-4 border-end px-1">
                            <div class="text-muted mb-2 text-truncate" style="font-size: 0.75rem; font-weight: 600;">Kipas 1</div>
                            <div class="device-fan-1 mb-2">
                                <i class="fas fa-fan fs-3 fan-icon {{ $fan1_on ? 'fan-spin text-primary' : 'text-secondary opacity-50' }}" id="fan1-icon-{{ $device->id }}"></i>
                            </div>
                            <span class="badge {{ $fan1_on ? 'bg-primary' : 'bg-secondary' }}" style="font-size: 0.65rem;" id="fan1-badge-{{ $device->id }}">{{ $fan1_on ? 'NYALA' : 'MATI' }}</span>
                        </div>
                        <div class="col-4 border-end px-1">
                            <div class="text-muted mb-2 text-truncate" style="font-size: 0.75rem; font-weight: 600;">Kipas 2</div>
                            <div class="device-fan-2 mb-2">
                                <i class="fas fa-fan fs-3 fan-icon {{ $fan2_on ? 'fan-spin text-primary' : 'text-secondary opacity-50' }}" id="fan2-icon-{{ $device->id }}"></i>
                            </div>
                            <span class="badge {{ $fan2_on ? 'bg-primary' : 'bg-secondary' }}" style="font-size: 0.65rem;" id="fan2-badge-{{ $device->id }}">{{ $fan2_on ? 'NYALA' : 'MATI' }}</span>
                        </div>
                        <div class="col-4 px-1">
                            <div class="text-muted mb-2 text-truncate" style="font-size: 0.75rem; font-weight: 600;">Penghangat</div>
                            <div class="device-heater mb-2">
                                <i class="fas fa-lightbulb fs-3 heater-icon {{ $heater_on ? 'lamp-glow text-warning' : 'text-secondary opacity-50' }}" id="heater-icon-{{ $device->id }}"></i>
                            </div>
                            <span class="badge {{ $heater_on ? 'bg-warning text-dark' : 'bg-secondary' }}" style="font-size: 0.65rem;" id="heater-badge-{{ $device->id }}">{{ $heater_on ? 'NYALA' : 'MATI' }}</span>
                        </div>
                    </div>
                    
                    <!-- Recommendations -->
                    @php
                        $recommendations = $monitoring->recommendation_list;
                    @endphp
                    @if(count($recommendations) > 0)
                        <div class="alert alert-danger mb-4 py-2 px-3 border-0" style="background-color: #fff5f5; border-left: 4px solid #dc3545 !important;">
                            <small class="fw-bold text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Rekomendasi Tindakan:</small>
                            <ul class="mb-0 mt-1 ps-3 text-danger" style="font-size: 0.85rem;">
                            @foreach($recommendations as $rec)
                                <li>{{ $rec }}</li>
                            @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- AC Control Widget -->
                    @include('dashboard.ac-control-widget', ['device' => $device, 'monitoring' => $monitoring])

                    <!-- Statistics for today -->
                    @if(isset($deviceStatistics[$device->id]))
                        <div class="device-stats mt-4 bg-light rounded-3 p-3 border">
                            <div class="text-muted mb-2" style="font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Statistik Hari Ini</div>
                            
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom" style="font-size: 0.85rem;">
                                <div><i class="fas fa-temperature-half text-danger"></i> Suhu</div>
                                <div class="text-end">
                                    <span class="fw-bold">{{ $deviceStatistics[$device->id]['avg_temp'] }}°C</span> (Rata)<br>
                                    <span class="text-muted" style="font-size: 0.75rem;">Max: {{ $deviceStatistics[$device->id]['max_temp'] }}° | Min: {{ $deviceStatistics[$device->id]['min_temp'] }}°</span>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2" style="font-size: 0.85rem;">
                                <div><i class="fas fa-droplet text-info"></i> Lembap</div>
                                <div class="text-end">
                                    <span class="fw-bold">{{ $deviceStatistics[$device->id]['avg_humidity'] }}%</span> (Rata)<br>
                                    <span class="text-muted" style="font-size: 0.75rem;">Max: {{ $deviceStatistics[$device->id]['max_humidity'] }}% | Min: {{ $deviceStatistics[$device->id]['min_humidity'] }}%</span>
                                </div>
                            </div>

                            @if($deviceStatistics[$device->id]['unsafe_count'] > 0)
                            <div class="mt-2 pt-2 border-top text-danger" style="font-size: 0.8rem;">
                                ⚠️ <strong>{{ $deviceStatistics[$device->id]['unsafe_count'] }} kali</strong> kondisi tidak normal hari ini
                            </div>
                            @endif
                        </div>
                    @endif

                    <div class="mt-4 text-center">
                        <span class="device-recorded-time badge bg-light text-muted border px-3 py-2 rounded-pill shadow-sm">
                            <i class="fas fa-clock me-1"></i> Terakhir diperbarui: 
                            @php
                                $diffMinutes = now()->diffInMinutes($monitoring->recorded_at);
                                if ($diffMinutes < 0) {
                                    echo 'sekarang';
                                } elseif ($diffMinutes === 0) {
                                    echo 'sekarang';
                                } elseif ($diffMinutes === 1) {
                                    echo '1 menit lalu';
                                } elseif ($diffMinutes < 60) {
                                    echo $diffMinutes . ' menit lalu';
                                } else {
                                    $hours = intval($diffMinutes / 60);
                                    echo ($hours === 1 ? '1 jam' : $hours . ' jam') . ' lalu';
                                }
                            @endphp
                        </span>
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">Belum ada data monitoring</p>
                    </div>
                @endif
            </div>
        </div>
            </div>
            
            <!-- Kolom Kanan: Grafik -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-chart-area text-primary me-2"></i> Grafik Suhu & Kelembapan</h5>
                                <span class="chart-live-badge live-badge offline"><span class="live-dot disconnected" style="width:7px;height:7px;margin:0;"></span> OFFLINE</span>
                            </div>
                            <div>
                                <select class="form-select form-select-sm border-0 bg-light chart-timeframe-select" data-device-id="{{ $device->id }}" style="width: auto; cursor: pointer;">
                                    <option value="1_hour">1 Jam Terakhir</option>
                                    <option value="6_hours">6 Jam Terakhir</option>
                                    <option value="12_hours">12 Jam Terakhir</option>
                                    <option value="1_day">24 Jam Terakhir</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-3 position-relative">
                        <!-- Loading Overlay: HIDDEN by default, ditampilkan via JS saat fetch -->
                        <div id="chart-loading-{{ $device->id }}"
                             style="display:none; position:absolute; z-index:10; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.93); border-radius:12px; flex-direction:column; justify-content:center; align-items:center; gap:8px;">
                            <div class="spinner-border text-primary" role="status" style="width:1.8rem;height:1.8rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <small class="text-muted fw-semibold" style="font-size:0.78rem;">Memuat grafik...</small>
                        </div>
                        
                        <!-- Chart Canvas -->
                        <div class="chart-container" style="position:relative; height:300px; width:100%;">
                            <canvas id="realtimeChart-{{ $device->id }}"></canvas>
                        </div>
                    </div>
                </div>
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

<!-- All Devices Status Indicator -->
@include('partials.devices-status')

<!-- Overview KPI Cards -->
<div class="row mb-4">
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
            <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #10b981, #34d399);"></div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 fw-bold" style="font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Total Ruangan</p>
                        <h2 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;">{{ count($devices) }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1);">
                        <i class="fas fa-door-open text-success fs-4"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 0.85rem;">
                    <i class="fas fa-check-circle text-success me-1"></i> <span class="fw-bold">{{ $safeCount }}</span> Ruangan Aman
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 col-sm-6 mb-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
            <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #ef4444, #f87171);"></div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 fw-bold" style="font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Ruangan Tidak Aman</p>
                        <h2 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;">{{ $unsafeCount }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(239, 68, 68, 0.1);">
                        <i class="fas fa-exclamation-circle text-danger fs-4"></i>
                    </div>
                </div>
                <div class="mt-3 text-muted" style="font-size: 0.85rem;">
                    @if($unsafeCount > 0)
                        <span class="text-danger fw-bold"><i class="fas fa-exclamation-triangle me-1"></i> Perlu tindakan segera</span>
                    @else
                        <i class="fas fa-shield-alt text-success me-1"></i> Sistem berjalan normal
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-12 mb-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
            <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #f59e0b, #fbbf24);"></div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted mb-1 fw-bold" style="font-size: 0.85rem; letter-spacing: 0.5px; text-transform: uppercase;">Insiden Hari Ini</p>
                        <h2 class="mb-0 fw-bold text-dark" style="font-size: 2.2rem;">{{ $todayUnsafeCount }}</h2>
                    </div>
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1);">
                        <i class="fas fa-bell text-warning fs-4"></i>
                    </div>
                </div>
                <div class="mt-3" style="font-size: 0.85rem;">
                    @if($todayUnsafeCount > 0)
                        <div class="text-muted">Terjadi di ruangan:</div>
                        <div class="d-flex flex-wrap gap-1 mt-1">
                            @foreach($unsafeByDevice as $deviceName => $count)
                                <span class="badge bg-light text-dark border">{{ $deviceName }} ({{ $count }}x)</span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-success"><i class="fas fa-check me-1"></i> Belum ada insiden tercatat</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Quick Actions -->
<div class="row mt-4 mb-5">
    <div class="col-12">
        <div class="d-flex align-items-center mb-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-bolt text-warning me-2"></i> Akses Cepat</h5>
        </div>
        <div class="row g-3">
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('monitoring.history') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-3 hover-lift">
                        <div class="mb-2"><i class="fas fa-history text-primary fs-3"></i></div>
                        <span class="text-dark fw-bold" style="font-size: 0.85rem;">Liwayat</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('monitoring.chart') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-3 hover-lift">
                        <div class="mb-2"><i class="fas fa-chart-area text-info fs-3"></i></div>
                        <span class="text-dark fw-bold" style="font-size: 0.85rem;">Grafik</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('monitoring.hourly-trend') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-3 hover-lift">
                        <div class="mb-2"><i class="fas fa-chart-line text-success fs-3"></i></div>
                        <span class="text-dark fw-bold" style="font-size: 0.85rem;">Tren Harian</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('reports.index') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-3 hover-lift">
                        <div class="mb-2"><i class="fas fa-file-pdf text-danger fs-3"></i></div>
                        <span class="text-dark fw-bold" style="font-size: 0.85rem;">Export PDF</span>
                    </div>
                </a>
            </div>
            @if(auth()->user()->role === 'admin')
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('device.index') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-3 hover-lift">
                        <div class="mb-2"><i class="fas fa-microchip text-secondary fs-3"></i></div>
                        <span class="text-dark fw-bold" style="font-size: 0.85rem;">Device</span>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <a href="{{ route('login-logs.index') }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm rounded-4 text-center p-3 hover-lift">
                        <div class="mb-2"><i class="fas fa-users-cog text-warning fs-3"></i></div>
                        <span class="text-dark fw-bold" style="font-size: 0.85rem;">Riwayat Login</span>
                    </div>
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .fan-spin {
        animation: spin 1s linear infinite;
        transform-origin: center;
    }
</style>

<!-- Recent Login Activity (for admin reference) -->
@if(auth()->user()->role === 'admin' && count($recentLoginLogs) > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sign-in-alt"></i> Aktivitas Login Terbaru</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
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
                </div>
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
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .fan-spin {
        animation: spin 1s linear infinite;
        transform-origin: center;
    }
    @keyframes glow {
        0% { filter: drop-shadow(0 0 2px rgba(255, 193, 7, 0.4)); opacity: 0.8; }
        50% { filter: drop-shadow(0 0 10px rgba(255, 193, 7, 0.9)); opacity: 1; }
        100% { filter: drop-shadow(0 0 2px rgba(255, 193, 7, 0.4)); opacity: 0.8; }
    }
    
    .lamp-glow {
        animation: glow 2s infinite alternate;
    }
</style>

<!-- ================================================================
     REAL-TIME DASHBOARD ENGINE
     - Poll setiap 5 detik dari /api/monitoring/dashboard/realtime
     - Chart append titik baru (bukan reload penuh)
     - Chart scrolling otomatis (max 60 titik = ~10 menit data)
     - Animasi flip nilai suhu & kelembaban
     - Live blinking dot indikator koneksi
================================================================ -->
<style>
/* ---- Live indicator dot ---- */
@keyframes blink-green {
    0%, 100% { opacity: 1; box-shadow: 0 0 0 0 rgba(34,197,94,0.6); }
    50%       { opacity: 0.7; box-shadow: 0 0 0 6px rgba(34,197,94,0); }
}
@keyframes blink-red {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.3; }
}
.live-dot {
    display: inline-block;
    width: 10px; height: 10px;
    border-radius: 50%;
    margin-right: 6px;
    vertical-align: middle;
    flex-shrink: 0;
}
.live-dot.connected    { background: #22c55e; animation: blink-green 1.2s ease infinite; }
.live-dot.disconnected { background: #ef4444; animation: blink-red  1s  linear infinite; }

/* ---- Nilai update: hanya bold, tidak ada animasi yang ganggu warna ---- */

/* ---- Live badge di header grafik ---- */
.live-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(34,197,94,0.12);
    color: #16a34a;
    border: 1px solid rgba(34,197,94,0.35);
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.4px;
}
.live-badge.offline {
    background: rgba(239,68,68,0.1);
    color: #dc2626;
    border-color: rgba(239,68,68,0.3);
}

/* ---- Fan spin ---- */
@keyframes spin { to { transform: rotate(360deg); } }
.fan-spin { animation: spin 0.8s linear infinite; transform-origin: center; }

/* ---- Heater glow ---- */
@keyframes glow {
    0%, 100% { filter: drop-shadow(0 0 2px rgba(251,191,36,0.4)); }
    50%       { filter: drop-shadow(0 0 10px rgba(251,191,36,1)); }
}
.lamp-glow { animation: glow 1.5s ease infinite; }

/* ---- Refresh spinner ---- */
@keyframes rotate { to { transform: rotate(360deg); } }
.spinning { animation: rotate 1s linear infinite; }

/* ---- hover card lift ---- */
.hover-lift { transition: transform 0.2s ease, box-shadow 0.2s ease; }
.hover-lift:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
</style>

<script>
// ================================================================
//  KONFIGURASI
// ================================================================
const RT_INTERVAL     = 3000;   // Poll API setiap 3 detik
const CHART_MAX_PTS   = 60;     // Maksimal titik di chart (60 × 10s = 10 menit)
const CHART_TIMEOUTS  = { '1_hour': 3600, '6_hours': 21600, '12_hours': 43200, '1_day': 86400 };

// ================================================================
//  STATE
// ================================================================
const deviceCharts        = {};   // { deviceId: Chart instance }
const chartDataBuffer     = {};   // { deviceId: { labels:[], temps:[], hums:[] } }
const lastSeenTimestamps  = {};   // { deviceId: ISO string } - deteksi data baru
const lastConnectionStates = {};
const connectionNotifShown = {};
let   pollInterval        = null;
let   clockInterval       = null;
let   isPollRunning       = false;

// ================================================================
//  BOOT
// ================================================================
document.addEventListener('DOMContentLoaded', function () {

    // Sembunyikan semua loading overlay
    document.querySelectorAll('[id^="chart-loading-"]').forEach(el => el.style.display = 'none');

    // Inisialisasi chart kosong untuk tiap device
    document.querySelectorAll('.device-monitor').forEach(el => {
        const did = el.dataset.deviceId;
        if (!did) return;

        chartDataBuffer[did] = { labels: [], temps: [], hums: [] };
        initRealtimeChart(did);

        // Event timeframe select → load ulang data historis
        const sel = el.querySelector('.chart-timeframe-select');
        if (sel) {
            sel.addEventListener('change', () => loadHistoricalChart(did, sel.value));
        }
    });

    // Load data historis pertama (1 jam terakhir)
    document.querySelectorAll('.device-monitor').forEach(el => {
        const did = el.dataset.deviceId;
        if (did) loadHistoricalChart(did, '1_hour');
    });

    // Jalankan polling realtime
    pollRealtimeData();
    pollInterval = setInterval(pollRealtimeData, RT_INTERVAL);

    // Live clock
    updateClock();
    clockInterval = setInterval(updateClock, 1000);

    console.log('🚀 Real-time dashboard aktif (interval=' + RT_INTERVAL + 'ms)');
});

// ================================================================
//  LIVE CLOCK
// ================================================================
function updateClock() {
    const el = document.getElementById('lastUpdateTime');
    if (el && !el.dataset.dirty) {
        const now = new Date();
        el.textContent = now.toLocaleTimeString('id-ID');
    }
}

// ================================================================
//  POLLING REALTIME
// ================================================================
async function pollRealtimeData() {
    if (isPollRunning) return;
    isPollRunning = true;

    // Spinner
    const spinner = document.getElementById('refreshSpinner');
    if (spinner) spinner.classList.add('spinning');

    try {
        const res = await fetch('/api/monitoring/dashboard/realtime', {
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json();

        if (json.success && Array.isArray(json.data)) {
            json.data.forEach(device => updateDeviceUI(device));
            // Update clock
            const el = document.getElementById('lastUpdateTime');
            if (el) {
                el.dataset.dirty = '1';
                el.textContent = new Date().toLocaleTimeString('id-ID');
                setTimeout(() => { if(el) delete el.dataset.dirty; }, 1500);
            }
        }
    } catch(e) {
        console.warn('⚠️ Poll error:', e.message);
    } finally {
        isPollRunning = false;
        if (spinner) spinner.classList.remove('spinning');
    }
}

// ================================================================
//  UPDATE UI SATU DEVICE
// ================================================================
function updateDeviceUI(device) {
    const card = document.querySelector(`[data-device-id="${device.id}"]`);
    if (!card) return;

    const isConnected  = device.is_connected;
    const temp         = device.temperature;
    const hum          = device.humidity;
    const lastUpdate   = device.last_update; // ISO string

    // ---- 1. Koneksi status ----
    const dot     = card.querySelector('.live-dot');
    const cLabel  = card.querySelector('.device-connection-status');
    const cTime   = card.querySelector('.device-last-update');
    const wifiIcon = card.querySelector('.device-connection-icon');

    if (dot) {
        dot.className = 'live-dot ' + (isConnected ? 'connected' : 'disconnected');
    }
    if (wifiIcon) {
        // Update ikon & warna WiFi
        wifiIcon.className = isConnected ? 'fas fa-wifi device-connection-icon' : 'fas fa-wifi-slash device-connection-icon';
        wifiIcon.style.color = isConnected ? '#22c55e' : '#ef4444';
    }
    if (cLabel) {
        cLabel.textContent = isConnected ? 'TERHUBUNG' : 'TIDAK TERHUBUNG';
        cLabel.style.color = isConnected ? '#22c55e' : '#ef4444';
    }
    if (cTime && device.minutes_ago !== null) {
        cTime.textContent = device.minutes_ago === 0 ? 'baru saja'
            : device.minutes_ago < 60 ? device.minutes_ago + ' mnt lalu'
            : Math.floor(device.minutes_ago / 60) + ' jam lalu';
    }

    // ---- 2. Live badge di header grafik ----
    const liveBadge = card.querySelector('.chart-live-badge');
    if (liveBadge) {
        if (isConnected) {
            liveBadge.className = 'live-badge';
            liveBadge.innerHTML = '<span class="live-dot connected" style="width:7px;height:7px;margin:0;"></span> LIVE';
        } else {
            liveBadge.className = 'live-badge offline';
            liveBadge.innerHTML = '<span class="live-dot disconnected" style="width:7px;height:7px;margin:0;"></span> OFFLINE';
        }
    }

    // ---- 3. Nilai suhu & kelembaban ----
    // Gunakan ID unik + style injection dengan !important untuk warna yang tidak bisa di-override
    const C_DANGER  = '#dc3545';
    const C_SUCCESS = '#198754';
    const C_OFFLINE = '#6c757d';

    if (temp !== null && temp !== undefined) {
        const tempNum  = parseFloat(temp);
        const tempStr  = isConnected ? tempNum.toFixed(1) : '0';
        const tempEl   = document.getElementById(`temp-val-${device.id}`);
        const tempColor = !isConnected ? C_OFFLINE
            : (tempNum < 15 || tempNum > 30) ? C_DANGER : C_SUCCESS;

        if (tempEl) {
            tempEl.textContent = tempStr;
            // Set warna di span angka DAN span satuan (°C) di sebelahnya
            tempEl.style.setProperty('color', tempColor, 'important');
            const unitEl = tempEl.nextElementSibling;
            if (unitEl) unitEl.style.setProperty('color', tempColor, 'important');
        }
    }

    if (hum !== null && hum !== undefined) {
        const humNum   = parseFloat(hum);
        const humStr   = isConnected ? humNum.toFixed(1) : '0';
        const humEl    = document.getElementById(`hum-val-${device.id}`);
        const humColor  = !isConnected ? C_OFFLINE
            : (humNum < 35 || humNum > 60) ? C_DANGER : C_SUCCESS;

        if (humEl) {
            humEl.textContent = humStr;
            // Set warna di span angka DAN span satuan (%) di sebelahnya
            humEl.style.setProperty('color', humColor, 'important');
            const unitEl = humEl.nextElementSibling;
            if (unitEl) unitEl.style.setProperty('color', humColor, 'important');
        }
    }

    // ---- 4. Status badge ----
    const sBadge = card.querySelector('.device-status-badge');
    const sText  = card.querySelector('.device-status-text');
    if (sBadge && sText && device.status) {
        sText.textContent = device.status;
        sBadge.classList.toggle('badge-aman',       device.status === 'Aman');
        sBadge.classList.toggle('badge-tidak-aman', device.status !== 'Aman');
    }

    // ---- 5. Kipas & Penghangat ----
    if (temp !== null) {
        const t   = parseFloat(temp);
        const on  = isConnected;
        const f1  = on && t >= 28;
        const f2  = on && t >  30;
        const htr = on && t <  28;

        setHardwareStatus(device.id, 'fan1',   f1,  'bg-primary', 'NYALA', 'MATI');
        setHardwareStatus(device.id, 'fan2',   f2,  'bg-primary', 'NYALA', 'MATI');
        setHardwareStatus(device.id, 'heater', htr, 'bg-warning text-dark', 'NYALA', 'MATI');
    }

    // ---- 6. Timestamp terakhir ----
    const recEl = card.querySelector('.device-recorded-time');
    if (recEl && lastUpdate) {
        recEl.innerHTML = `<i class="fas fa-clock me-1"></i> Terakhir diperbarui: ${getRelativeTime(new Date(lastUpdate))}`;
    }

    // ---- 7. Append ke chart jika ada data BARU ----
    if (isConnected && temp !== null && lastUpdate) {
        const prev = lastSeenTimestamps[device.id];
        if (prev !== lastUpdate) {
            lastSeenTimestamps[device.id] = lastUpdate;
            appendChartPoint(device.id, lastUpdate, temp, hum);
        }
    } else if (!isConnected) {
        // Device offline → tidak append
    }

    // ---- 8. Notifikasi koneksi berubah ----
    const wasConn = lastConnectionStates[device.id];
    if (wasConn !== undefined && wasConn !== isConnected) {
        if (isConnected) showNotif('espConnectedAlert', 'espConnectedMessage',
            `✓ ${device.device_name} TERHUBUNG – Data diterima`);
        else showNotif('espDisconnectedAlert', 'espDisconnectedMessage',
            `⚠️ ${device.device_name} TIDAK TERHUBUNG – Periksa koneksi WiFi`, false);
    }
    lastConnectionStates[device.id] = isConnected;
}

// ================================================================
//  CHART: INISIALISASI KOSONG (REAL-TIME MODE)
// ================================================================
function initRealtimeChart(deviceId) {
    const canvas = document.getElementById(`realtimeChart-${deviceId}`);
    if (!canvas || deviceCharts[deviceId]) return;

    const ctx = canvas.getContext('2d');

    const tempGrad = ctx.createLinearGradient(0, 0, 0, 300);
    tempGrad.addColorStop(0, 'rgba(239,68,68,0.28)');
    tempGrad.addColorStop(1, 'rgba(239,68,68,0.02)');

    const humGrad = ctx.createLinearGradient(0, 0, 0, 300);
    humGrad.addColorStop(0, 'rgba(59,130,246,0.22)');
    humGrad.addColorStop(1, 'rgba(59,130,246,0.02)');

    deviceCharts[deviceId] = new Chart(canvas, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Suhu (°C)',
                    data: [],
                    borderColor: '#ef4444',
                    backgroundColor: tempGrad,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Kelembapan (%)',
                    data: [],
                    borderColor: '#3b82f6',
                    backgroundColor: humGrad,
                    borderWidth: 2.5,
                    borderDash: [6, 3],
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 400, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true, pointStyle: 'circle',
                        boxWidth: 10, padding: 20,
                        font: { size: 12, weight: '600' }, color: '#374151'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.92)',
                    titleColor: '#f1f5f9',
                    bodyColor: '#94a3b8',
                    borderColor: 'rgba(255,255,255,0.08)',
                    borderWidth: 1,
                    padding: 12, boxPadding: 6,
                    usePointStyle: true, cornerRadius: 10,
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y !== undefined ? ctx.parsed.y.toFixed(1) : '--'}${ctx.datasetIndex === 0 ? '°C' : '%'}`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false }, border: { display: false },
                    ticks: { maxRotation: 0, maxTicksLimit: 8, color: '#9ca3af', font: { size: 10 } }
                },
                y: {
                    type: 'linear', position: 'left',
                    title: { display: true, text: 'Suhu (°C)', color: '#ef4444', font: { size: 11, weight: '600' } },
                    grid: { color: 'rgba(0,0,0,0.04)' }, border: { display: false },
                    ticks: { color: '#ef4444', font: { size: 11 } },
                    suggestedMin: 20, suggestedMax: 40
                },
                y1: {
                    type: 'linear', position: 'right',
                    title: { display: true, text: 'Kelembapan (%)', color: '#3b82f6', font: { size: 11, weight: '600' } },
                    grid: { drawOnChartArea: false }, border: { display: false },
                    ticks: { color: '#3b82f6', font: { size: 11 } },
                    suggestedMin: 20, suggestedMax: 100
                }
            }
        }
    });
}

// ================================================================
//  CHART: APPEND TITIK BARU (REAL-TIME SCROLL)
// ================================================================
function appendChartPoint(deviceId, isoTime, temp, hum) {
    const chart = deviceCharts[deviceId];
    if (!chart) return;

    const label = new Date(isoTime).toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    });

    // Simpan di buffer
    const buf = chartDataBuffer[deviceId];
    buf.labels.push(label);
    buf.temps.push(parseFloat(temp));
    buf.hums.push(parseFloat(hum));

    // Trim jika melebihi batas
    if (buf.labels.length > CHART_MAX_PTS) {
        buf.labels.shift();
        buf.temps.shift();
        buf.hums.shift();
    }

    // Update chart tanpa animasi panjang (lebih smooth)
    chart.data.labels              = [...buf.labels];
    chart.data.datasets[0].data   = [...buf.temps];
    chart.data.datasets[1].data   = [...buf.hums];
    chart.update('none'); // 'none' = tanpa animasi supaya lebih cepat
}

// ================================================================
//  CHART: LOAD HISTORIS (saat pertama buka / ganti timeframe)
// ================================================================
function loadHistoricalChart(deviceId, timeframe) {
    const loading = document.getElementById(`chart-loading-${deviceId}`);

    // Show: gunakan 'flex' karena overlay pakai flex layout via inline style
    const showLoading  = () => { if (loading) loading.style.display = 'flex'; };
    const hideLoading  = () => { if (loading) loading.style.display = 'none'; };

    // Pastikan tersembunyi dulu jika sebelumnya masih tampil
    hideLoading();

    // Tampilkan loading
    showLoading();

    fetch(`/api/monitoring/get-chart-data?device_id=${deviceId}&timeframe=${timeframe}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
    .then(res => {
        if (!res.success || !res.data) return;
        const { dates, temperatures, humidities } = res.data;

        // Isi buffer dengan data historis
        chartDataBuffer[deviceId] = {
            labels: [...(dates || [])],
            temps:  [...(temperatures || [])],
            hums:   [...(humidities || [])]
        };

        // Trim jika lebih dari batas
        const buf = chartDataBuffer[deviceId];
        while (buf.labels.length > CHART_MAX_PTS) {
            buf.labels.shift(); buf.temps.shift(); buf.hums.shift();
        }

        const chart = deviceCharts[deviceId];
        if (chart) {
            chart.data.labels            = [...buf.labels];
            chart.data.datasets[0].data  = [...buf.temps];
            chart.data.datasets[1].data  = [...buf.hums];
            chart.update();
        }
    })
    .catch(e => console.warn('⚠️ Chart load error:', e.message))
    .finally(() => { hideLoading(); });
}

// ================================================================
//  HELPER: Animasi nilai berubah
// ================================================================
function animateValue(el, newVal) {
    if (!el) return;

    // Bandingkan secara numerik untuk menghindari false-mismatch
    // antara format PHP (koma) vs JS (titik), misal "87,0" vs "87.0"
    const currentNum = parseFloat(el.textContent);
    const newNum     = parseFloat(newVal);
    const valueChanged = isNaN(currentNum) || Math.abs(currentNum - newNum) >= 0.05;

    // Selalu update text agar format konsisten (titik, bukan koma)
    el.textContent = String(newVal);

    // Animasi HANYA jika nilai benar-benar berubah
    // Ini mencegah opacity-0 flash setiap 3 detik saat nilai sama
    if (valueChanged) {
        el.classList.remove('value-updated');
        void el.offsetWidth; // force reflow
        el.classList.add('value-updated');
    }
}

// ================================================================
//  HELPER: Set status kipas/penghangat
// ================================================================
function setHardwareStatus(deviceId, type, isOn, onBadgeClass, onLabel, offLabel) {
    const icon  = document.getElementById(`${type}-icon-${deviceId}`);
    const badge = document.getElementById(`${type}-badge-${deviceId}`);
    if (!icon || !badge) return;

    if (type === 'heater') {
        icon.classList.toggle('lamp-glow',  isOn);
        icon.classList.toggle('text-warning', isOn);
        icon.classList.toggle('text-secondary', !isOn);
        icon.classList.toggle('opacity-50', !isOn);
    } else {
        icon.classList.toggle('fan-spin', isOn);
        icon.classList.toggle('text-primary', isOn);
        icon.classList.toggle('text-secondary', !isOn);
        icon.classList.toggle('opacity-50', !isOn);
    }

    badge.className = 'badge ' + (isOn ? onBadgeClass : 'bg-secondary');
    badge.textContent = isOn ? onLabel : offLabel;
}

// ================================================================
//  HELPER: Relative time
// ================================================================
function getRelativeTime(date) {
    const ms   = Date.now() - date.getTime();
    const secs = Math.floor(ms / 1000);
    const mins = Math.floor(secs / 60);
    const hrs  = Math.floor(mins / 60);
    if (secs < 10)  return 'baru saja';
    if (secs < 60)  return secs + ' detik lalu';
    if (mins < 60)  return mins + ' menit lalu';
    if (hrs  < 24)  return hrs  + ' jam lalu';
    return Math.floor(hrs / 24) + ' hari lalu';
}

// ================================================================
//  HELPER: Notifikasi
// ================================================================
function showNotif(alertId, msgId, text, autoHide = true) {
    const alertEl = document.getElementById(alertId);
    const msgEl   = document.getElementById(msgId);
    if (!alertEl || !msgEl) return;
    msgEl.textContent = text;
    alertEl.classList.remove('d-none');
    if (autoHide) setTimeout(() => alertEl.classList.add('d-none'), 5000);
}

// ================================================================
//  CLEANUP
// ================================================================
window.addEventListener('beforeunload', () => {
    clearInterval(pollInterval);
    clearInterval(clockInterval);
});
</script>
@endsection
