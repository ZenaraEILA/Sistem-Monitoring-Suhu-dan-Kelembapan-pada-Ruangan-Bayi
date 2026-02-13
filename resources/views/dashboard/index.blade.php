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

<!-- ESP8266 Connected Notification -->
<div id="espConnectedAlert" class="alert alert-success alert-dismissible fade show d-none" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <span id="espConnectedMessage">ESP8266 TERHUBUNG - Data diterima</span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- ESP8266 Disconnected Notification -->
<div id="espDisconnectedAlert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i>
    <span id="espDisconnectedMessage">ESP8266 TIDAK TERHUBUNG - Periksa koneksi WiFi</span>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
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

<!-- Export Laporan Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="fas fa-download"></i> Export Laporan Monitoring</h6>
                        <small class="text-muted">Unduh laporan dalam bentuk PDF atau Excel untuk dokumentasi medis</small>
                    </div>
                    <a href="{{ route('reports.index') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-export"></i> Manage Export »
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Devices Monitoring -->
<div class="row" id="devicesContainer">
    @forelse($devices as $device)
    <div class="col-md-6 col-lg-4 mb-4 device-monitor" data-device-id="{{ $device->id }}">
        <div class="device-card card h-100">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $device->device_name }}</h5>
                    <span class="badge device-status-badge {{ $device->monitorings->count() > 0 && $device->monitorings->first()->status === 'Aman' ? 'badge-aman' : 'badge-tidak-aman' }}">
                        <span class="device-status-text">{{ $device->monitorings->count() > 0 ? $device->monitorings->first()->status : 'No Data' }}</span>
                    </span>
                </div>
                <small class="text-white-50"><i class="fas fa-map-marker-alt"></i> {{ $device->location }}</small>
            </div>

            <!-- Connection Status Indicator -->
            <div class="card-header bg-light border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        <i class="fas fa-wifi device-connection-icon" style="color: #28a745;"></i>
                        <span class="device-connection-status">TERHUBUNG</span>
                    </small>
                    <small class="text-muted device-last-update">
                        sekarang
                    </small>
                </div>
            </div>

            <div class="card-body">
                @if($device->monitorings->count() > 0)
                    @php
                        $monitoring = $device->monitorings->first();
                    @endphp
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">Suhu</small>
                            <div class="temp-display device-temperature {{ $monitoring->temperature < 15 || $monitoring->temperature > 30 ? 'text-danger' : 'text-success' }}">
                                <span class="device-temp-value">{{ number_format($monitoring->temperature, 1) }}</span>°C
                            </div>
                            <small class="text-success">✓ Normal: 15-30°C</small>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Kelembapan</small>
                            <div class="humidity-display device-humidity {{ $monitoring->humidity < 35 || $monitoring->humidity > 60 ? 'text-danger' : 'text-success' }}">
                                <span class="device-humidity-value">{{ number_format($monitoring->humidity, 1) }}</span>%
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

                    <!-- AC Control Widget -->
                    @include('dashboard.ac-control-widget', ['device' => $device, 'monitoring' => $monitoring])

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
                    <small class="text-muted device-recorded-time">
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
                    <a href="{{ route('reports.index') }}" class="btn btn-outline-success">
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

<!-- Realtime Dashboard JavaScript -->
<script>
// Realtime dashboard polling for ESP8266 connection status & data updates
const POLLING_INTERVAL = 10000; // 10 seconds
let lastConnectionStates = {}; // Track previous connection states
let connectionNotificationShown = {}; // Track which devices have shown notifications

// Start polling on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard realtime monitoring started');
    fetchRealtimeData(); // Fetch immediately
    setInterval(fetchRealtimeData, POLLING_INTERVAL); // Then poll every 10 seconds
});

// Fetch realtime data from API
function fetchRealtimeData() {
    fetch('/api/monitoring/dashboard/realtime')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateRealtimeDashboard(data.data);
                updateLastUpdateTime(data.timestamp);
            }
        })
        .catch(error => {
            console.error('Error fetching realtime data:', error);
        });
}

// Update dashboard with realtime data
function updateRealtimeDashboard(devicesData) {
    devicesData.forEach(device => {
        const deviceCard = document.querySelector(`[data-device-id="${device.id}"]`);
        
        if (!deviceCard) {
            console.warn(`Device card not found for ID: ${device.id}`);
            return;
        }

        // Update temperature & humidity
        if (device.temperature !== null) {
            const tempElement = deviceCard.querySelector('.device-temp-value');
            const humidityElement = deviceCard.querySelector('.device-humidity-value');
            
            if (tempElement) {
                tempElement.textContent = device.temperature.toFixed(1);
                // Update color based on safe range
                const tempDisplay = deviceCard.querySelector('.device-temperature');
                if (device.temperature < 15 || device.temperature > 30) {
                    tempDisplay.classList.remove('text-success');
                    tempDisplay.classList.add('text-danger');
                } else {
                    tempDisplay.classList.remove('text-danger');
                    tempDisplay.classList.add('text-success');
                }
            }
            
            if (humidityElement) {
                humidityElement.textContent = device.humidity.toFixed(1);
                // Update color based on safe range
                const humidityDisplay = deviceCard.querySelector('.device-humidity');
                if (device.humidity < 35 || device.humidity > 60) {
                    humidityDisplay.classList.remove('text-success');
                    humidityDisplay.classList.add('text-danger');
                } else {
                    humidityDisplay.classList.remove('text-danger');
                    humidityDisplay.classList.add('text-success');
                }
            }
        }

        // Update connection status
        const connectionIcon = deviceCard.querySelector('.device-connection-icon');
        const connectionStatus = deviceCard.querySelector('.device-connection-status');
        const lastUpdateElement = deviceCard.querySelector('.device-last-update');
        
        if (connectionIcon && connectionStatus) {
            const wasConnected = lastConnectionStates[device.id] !== false;
            const isNowConnected = device.is_connected;
            
            if (isNowConnected) {
                connectionIcon.style.color = '#28a745'; // Green
                connectionStatus.textContent = 'TERHUBUNG';
                connectionIcon.className = 'fas fa-wifi device-connection-icon';
                
                // Show connected notification only if status changed
                if (!wasConnected || !connectionNotificationShown[device.id]) {
                    showConnectedNotification(device.device_name);
                    connectionNotificationShown[device.id] = true;
                }
            } else {
                connectionIcon.style.color = '#dc3545'; // Red
                connectionStatus.textContent = 'TIDAK TERHUBUNG';
                connectionIcon.className = 'fas fa-wifi-off device-connection-icon';
                
                // Show disconnected notification only if status changed
                if (wasConnected || !connectionNotificationShown[device.id]) {
                    showDisconnectedNotification(device.device_name);
                    connectionNotificationShown[device.id] = true;
                }
            }
            
            lastConnectionStates[device.id] = isNowConnected;
        }

        // Update last update time
        if (lastUpdateElement && device.minutes_ago !== null) {
            let timeText = '';
            if (device.minutes_ago === 0) {
                timeText = 'sekarang';
            } else if (device.minutes_ago === 1) {
                timeText = '1 menit lalu';
            } else if (device.minutes_ago < 60) {
                timeText = `${device.minutes_ago} menit lalu`;
            } else {
                const hours = Math.floor(device.minutes_ago / 60);
                timeText = hours === 1 ? '1 jam lalu' : `${hours} jam lalu`;
            }
            lastUpdateElement.textContent = timeText;
        }

        // Update status badge
        const statusBadge = deviceCard.querySelector('.device-status-badge');
        const statusText = deviceCard.querySelector('.device-status-text');
        if (statusBadge && statusText && device.status) {
            statusText.textContent = device.status;
            if (device.status === 'Aman') {
                statusBadge.classList.remove('badge-tidak-aman');
                statusBadge.classList.add('badge-aman');
            } else {
                statusBadge.classList.remove('badge-aman');
                statusBadge.classList.add('badge-tidak-aman');
            }
        }

        // Update recorded time
        const recordedTime = deviceCard.querySelector('.device-recorded-time');
        if (recordedTime && device.last_update) {
            const lastUpdate = new Date(device.last_update);
            recordedTime.textContent = `⏰ Terakhir diperbarui: ${getRelativeTime(lastUpdate)}`;
        }
    });
}

// Show ESP8266 connected notification
function showConnectedNotification(deviceName) {
    const alertEl = document.getElementById('espConnectedAlert');
    const messageEl = document.getElementById('espConnectedMessage');
    
    messageEl.textContent = `✓ ESP8266 "${deviceName}" TERHUBUNG - Data diterima`;
    alertEl.classList.remove('d-none');
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        alertEl.classList.add('d-none');
    }, 5000);
}

// Show ESP8266 disconnected notification
function showDisconnectedNotification(deviceName) {
    const alertEl = document.getElementById('espDisconnectedAlert');
    const messageEl = document.getElementById('espDisconnectedMessage');
    
    messageEl.textContent = `⚠️ ESP8266 "${deviceName}" TIDAK TERHUBUNG - Periksa koneksi WiFi`;
    alertEl.classList.remove('d-none');
    
    // Do NOT auto-hide - user should dismiss manually
}

// Update the global "last updated" time
function updateLastUpdateTime(timestamp) {
    const element = document.getElementById('lastUpdateTime');
    if (element) {
        const time = new Date(timestamp);
        element.textContent = getRelativeTime(time);
    }
}

// Get relative time format (e.g., "2 menit lalu")
function getRelativeTime(date) {
    const now = new Date();
    const diffMs = now - new Date(date);
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMins === 0) return 'sekarang';
    if (diffMins === 1) return '1 menit lalu';
    if (diffMins < 60) return `${diffMins} menit lalu`;
    if (diffHours === 1) return '1 jam lalu';
    if (diffHours < 24) return `${diffHours} jam lalu`;
    if (diffDays === 1) return 'kemarin';
    return `${diffDays} hari lalu`;
}

// Add rotation animation to refresh spinner during polling
const originalFetch = window.fetch;
window.fetch = function(...args) {
    const spinner = document.getElementById('refreshSpinner');
    if (spinner) {
        spinner.style.animation = 'spin 1s linear infinite';
    }
    
    return originalFetch.apply(this, args).then(response => {
        if (spinner) {
            spinner.style.animation = 'none';
        }
        return response;
    });
};

// Add CSS for spinner animation
const style = document.createElement('style');
style.textContent = `
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
`;
document.head.appendChild(style);
</script>
@endsection
