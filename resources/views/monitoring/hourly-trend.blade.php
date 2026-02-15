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
            <div class="col-md-3">
                <label for="device_id" class="form-label">Ruangan/Device</label>
                <select name="device_id" id="device_id" class="form-select">
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" {{ $device->id == $selectedDevice ? 'selected' : '' }}>
                            {{ $device->device_name }} ({{ $device->location }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="date" class="form-label">Tanggal</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-md-2">
                <label for="start_time" class="form-label">Jam Mulai</label>
                <input type="time" name="start_time" id="start_time" class="form-control" value="{{ $startTime ?? '' }}">
            </div>
            <div class="col-md-2">
                <label for="end_time" class="form-label">Jam Akhir</label>
                <input type="time" name="end_time" id="end_time" class="form-control" value="{{ $endTime ?? '' }}">
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Cari
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

    <!-- Time Range Info -->
    @php
        $displayStartTime = $startTime ?? '00:00';
        $displayEndTime = $endTime ?? '23:59';
    @endphp
    <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-clock me-2"></i>
        <strong>Rentang Waktu:</strong> 
        {{ $displayStartTime }} - {{ $displayEndTime }} 
        pada tanggal {{ date('d/m/Y', strtotime($date)) }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

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

    <!-- Professional Chart Container - MetaTrader 5 Style -->
    <div class="card mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%); color: white;">
            <h5 class="mb-0" style="color: white;"><i class="fas fa-chart-candle"></i> Grafik Analisis Tren Candle</h5>
        </div>
        <div class="card-body" style="background-color: #FFFFFF;">
            <div id="candleChart" style="height: 500px;"></div>
        </div>
    </div>

    <!-- Secondary Chart: Temperature & Humidity Overlay -->
    <div class="card mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%); color: white;">
            <h5 class="mb-0" style="color: white;"><i class="fas fa-wave-square"></i> Grafik Suhu & Kelembapan Overlay</h5>
        </div>
        <div class="card-body" style="background-color: #FFFFFF;">
            <div id="overlayChart" style="height: 400px;"></div>
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
                        
                        // Temperature color - solid merah
                        $tempColor = '#E74C3C'; // Merah profesional
                        
                        // Humidity color - solid biru
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
<!-- ApexCharts Library - Professional Financial Charts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hourlyData = @json($hourlyData);
        
        if (!hourlyData || hourlyData.length === 0) {
            console.log('No data available for charts');
            return;
        }

        // Prepare candlestick data
        const candleData = hourlyData.map((d, idx) => ({
            x: new Date(new Date().toDateString() + ' ' + (String(d.hour).padStart(2, '0')) + ':00'),
            y: [
                d.min_temp || 0,      // Open
                d.max_temp || 0,      // High
                d.min_temp || 0,      // Low
                d.avg_temp || 0       // Close
            ]
        }));

        // Prepare overlay data
        const labels = hourlyData.map(d => (String(d.hour).padStart(2, '0')) + ':00');
        const avgTemps = hourlyData.map(d => parseFloat(d.avg_temp) || 0);
        const avgHumidities = hourlyData.map(d => parseFloat(d.avg_humidity) || 0);

        // Chart 1: Candlestick Chart (MetaTrader 5 Style)
        const candleOptions = {
            chart: {
                type: 'candlestick',
                height: 500,
                zoom: { enabled: true },
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    }
                },
                background: '#FFFFFF'
            },
            title: {
                text: 'Analisis Tren Suhu (Candlestick)',
                style: {
                    color: '#333',
                    fontSize: '14px'
                }
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    style: { colors: '#666', fontSize: '11px' }
                },
                axisBorder: { color: '#ECEFF1' },
                axisTicks: { color: '#ECEFF1' }
            },
            yaxis: {
                title: {
                    text: 'Suhu (°C)',
                    style: { color: '#E74C3C', fontSize: '12px', fontWeight: 'bold' }
                },
                labels: {
                    style: { colors: '#E74C3C', fontSize: '11px' }
                }
            },
            plotOptions: {
                candlestick: {
                    colors: {
                        upward: '#0056b3',    // Dark blue for up
                        downward: '#e8f0ff'    // Light blue for down
                    },
                    wick: {
                        useFillColor: true
                    }
                }
            },
            grid: {
                borderColor: '#ECEFF1',
                strokeDashArray: 3,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: true } }
            },
            tooltip: {
                theme: 'dark',
                y: {
                    formatter: function(val) {
                        return val.toFixed(2) + '°C';
                    }
                }
            }
        };

        // Series data for candlestick
        const candleSeries = [{
            data: candleData
        }];

        // Render Candlestick Chart
        const candleChart = new ApexCharts(document.querySelector('#candleChart'), {
            ...candleOptions,
            series: candleSeries
        });

        candleChart.render();

        // Chart 2: Area Chart with dual axis (Overlay)
        const overlayOptions = {
            chart: {
                type: 'area',
                height: 400,
                zoom: { enabled: true },
                toolbar: { show: true },
                background: '#FFFFFF'
            },
            colors: ['#E74C3C', '#3498DB'],
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                }
            },
            xaxis: {
                categories: labels,
                labels: {
                    style: { fontSize: '11px' }
                }
            },
            yaxis: [
                {
                    title: {
                        text: 'Suhu (°C)',
                        style: { color: '#E74C3C', fontSize: '12px', fontWeight: 'bold' }
                    },
                    labels: {
                        style: { color: '#E74C3C' }
                    }
                },
                {
                    opposite: true,
                    title: {
                        text: 'Kelembapan (%)',
                        style: { color: '#3498DB', fontSize: '12px', fontWeight: 'bold' }
                    },
                    labels: {
                        style: { color: '#3498DB' }
                    }
                }
            ],
            tooltip: {
                shared: true,
                theme: 'dark'
            },
            legend: {
                position: 'top',
                fontSize: '12px'
            },
            grid: {
                borderColor: '#ECEFF1',
                strokeDashArray: 4
            }
        };

        const overlaySeries = [
            {
                name: 'Suhu (°C)',
                data: avgTemps
            },
            {
                name: 'Kelembapan (%)',
                data: avgHumidities
            }
        ];

        // Render Overlay Chart
        const overlayChart = new ApexCharts(document.querySelector('#overlayChart'), {
            ...overlayOptions,
            series: overlaySeries
        });

        overlayChart.render();

        console.log('✅ ApexCharts rendered successfully - MetaTrader 5 Style!');
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

    /* ApexCharts Custom Styling */
    .apexcharts-menu {
        background-color: #2a2a2a !important;
        border-color: #444 !important;
    }

    .apexcharts-menu-item {
        color: #fff !important;
    }

    .apexcharts-menu-item:hover {
        background-color: #444 !important;
    }
</style>

@endsection
