@extends('layouts.main')

@section('title', 'Tren Harian - Sistem Monitoring')

@section('content')

<!-- Page Header -->
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0"><i class="fas fa-chart-line"></i> Analisis Tren Harian</h1>
        <small class="text-muted">Monitoring detail per jam untuk tanggal yang dipilih</small>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Data</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('monitoring.hourly-trend') }}" method="get" class="row g-3">
            <div class="col-md-4">
                <label for="device_id" class="form-label">Ruangan/Device</label>
                <select name="device_id" id="device_id" class="form-select">
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" {{ $device->id == $selectedDevice ? 'selected' : '' }}>
                            {{ $device->device_name }} ({{ $device->location }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="date" class="form-label">Tanggal</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-md-4">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Tampilkan Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Check if data exists -->
@if(count($hourlyData) > 0)
    @php
        // Calculate statistics
        $temps = $hourlyData->pluck('avg_temp')->filter(function($v) { return !is_null($v) && $v > 0; });
        $humidities = $hourlyData->pluck('avg_humidity')->filter(function($v) { return !is_null($v) && $v > 0; });
        $maxTemps = $hourlyData->pluck('max_temp')->filter(function($v) { return !is_null($v) && $v > 0; });
        $minTemps = $hourlyData->pluck('min_temp')->filter(function($v) { return !is_null($v) && $v > 0; });
        
        $avgTemp = $temps->count() > 0 ? round($temps->avg(), 1) : '--';
        $maxTemp = $maxTemps->count() > 0 ? round($maxTemps->max(), 1) : '--';
        $minTemp = $minTemps->count() > 0 ? round($minTemps->min(), 1) : '--';
        $avgHumidity = $humidities->count() > 0 ? round($humidities->avg(), 0) : '--';
    @endphp

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <small class="text-muted d-block fw-bold">Rata-rata Suhu</small>
                            <h3 class="mb-0" style="color: #E74C3C;">{{ $avgTemp }}°C</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="fas fa-thermometer-half fa-3x" style="color: #E74C3C; opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <small class="text-muted d-block fw-bold">Suhu Maksimal</small>
                            <h3 class="mb-0" style="color: #E74C3C;">{{ $maxTemp }}°C</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="fas fa-arrow-up fa-3x" style="color: #E74C3C; opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <small class="text-muted d-block fw-bold">Suhu Minimal</small>
                            <h3 class="mb-0" style="color: #E74C3C;">{{ $minTemp }}°C</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="fas fa-arrow-down fa-3x" style="color: #E74C3C; opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <small class="text-muted d-block fw-bold">Rata-rata Kelembapan</small>
                            <h3 class="mb-0" style="color: #3498DB;">{{ $avgHumidity }}%</h3>
                        </div>
                        <div class="ms-auto">
                            <i class="fas fa-tint fa-3x" style="color: #3498DB; opacity: 0.2;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-chart-line"></i> Grafik Tren Per Jam</h5>
        </div>
        <div class="card-body">
            <canvas id="trendChart" height="80"></canvas>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-table"></i> Detail Per Jam</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 100px;">Jam</th>
                        <th>Rata-rata</th>
                        <th>Min - Max</th>
                        <th style="width: 300px;">Visualisasi Suhu</th>
                        <th>Kelembapan</th>
                        <th style="width: 300px;">Visualisasi Kelembapan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hourlyData as $data)
                    @php
                        $avgTemp = $data->avg_temp ?? 0;
                        $minTemp = $data->min_temp ?? 0;
                        $maxTemp = $data->max_temp ?? 0;
                        $avgHum = $data->avg_humidity ?? 0;
                        
                        // Temperature color - solid merah untuk consistency dengan grafik
                        $tempColor = '#E74C3C'; // Merah profesional
                        
                        // Humidity color - solid biru untuk consistency dengan grafik
                        $humColor = '#3498DB'; // Biru profesional
                        
                        // Percentage for bar
                        $tempPercent = min(max(($avgTemp / 40) * 100, 0), 100);
                        $humPercent = min(max(($avgHum / 100) * 100, 0), 100);
                    @endphp
                    <tr>
                        <td class="fw-bold">{{ sprintf('%02d', $data->hour) }}:00</td>
                        <td>
                            <span style="color: {{ $tempColor }}; font-weight: bold; font-size: 1.1em;">
                                {{ number_format($avgTemp, 1) }}°C
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ number_format($minTemp, 1) }}°C - {{ number_format($maxTemp, 1) }}°C
                            </small>
                        </td>
                        <td>
                            <div style="background-color: #f0f0f0; border-radius: 4px; overflow: hidden; height: 30px; position: relative;">
                                <div style="width: {{ $tempPercent }}%; height: 100%; background-color: {{ $tempColor }}; display: flex; align-items: center; justify-content: flex-end; padding-right: 8px;">
                                    @if($tempPercent > 10)
                                    <small style="color: white; font-weight: bold; font-size: 11px;">{{ round($tempPercent) }}%</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="color: {{ $humColor }}; font-weight: bold; font-size: 1.1em;">
                                {{ number_format($avgHum, 0) }}%
                            </span>
                        </td>
                        <td>
                            <div style="background-color: #f0f0f0; border-radius: 4px; overflow: hidden; height: 30px; position: relative;">
                                <div style="width: {{ $humPercent }}%; height: 100%; background-color: {{ $humColor }}; display: flex; align-items: center; justify-content: flex-end; padding-right: 8px;">
                                    @if($humPercent > 10)
                                    <small style="color: white; font-weight: bold; font-size: 11px;">{{ round($humPercent) }}%</small>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@else
    <!-- No Data Message -->
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Tidak ada data</strong> untuk tanggal dan device yang dipilih. Silakan pilih tanggal lain atau tunggu hingga ada data monitoring.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('trendChart');
        
        // Only render chart if canvas exists and data exists
        if (!canvas) return;
        
        const hourlyData = @json($hourlyData);
        if (!hourlyData || hourlyData.length === 0) return;
        
        // Prepare chart data
        const labels = hourlyData.map(d => (String(d.hour).padStart(2, '0')) + ':00');
        const avgTemps = hourlyData.map(d => parseFloat(d.avg_temp) || 0);
        const maxTemps = hourlyData.map(d => parseFloat(d.max_temp) || 0);
        const minTemps = hourlyData.map(d => parseFloat(d.min_temp) || 0);
        const avgHums = hourlyData.map(d => parseFloat(d.avg_humidity) || 0);

        // Render chart
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Rata-rata Suhu (°C)',
                        data: avgTemps,
                        borderColor: '#E74C3C',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#dc3545',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Suhu Maksimal (°C)',
                        data: maxTemps,
                        borderColor: 'rgba(231, 76, 60, 0.5)',
                        borderWidth: 1,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Suhu Minimal (°C)',
                        data: minTemps,
                        borderColor: 'rgba(231, 76, 60, 0.3)',
                        borderWidth: 1,
                        borderDash: [5, 5],
                        fill: false,
                        pointRadius: 0,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Rata-rata Kelembapan (%)',
                        data: avgHums,
                        borderColor: '#3498DB',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#0dcaf0',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: { size: 11, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 10,
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 10 }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Suhu (°C)',
                            color: '#E74C3C',
                            font: { size: 11, weight: 'bold' }
                        },
                        ticks: { color: '#E74C3C', font: { weight: '600' } },
                        grid: { color: 'rgba(231, 76, 60, 0.1)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Kelembapan (%)',
                            color: '#3498DB',
                            font: { size: 11, weight: 'bold' }
                        },
                        ticks: { color: '#3498DB', font: { weight: '600' } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>

<style>
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    canvas {
        display: block !important;
    }
</style>

@endsection
