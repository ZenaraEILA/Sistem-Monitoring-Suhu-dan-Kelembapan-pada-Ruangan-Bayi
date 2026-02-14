@extends('layouts.main')

@section('title', 'Grafik Monitoring - Sistem Monitoring Suhu Bayi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0"><i class="fas fa-chart-area"></i> Grafik Monitoring Interaktif</h1>
        <small class="text-muted">Tampilan grafik real-time data suhu dan kelembapan bayi.</small>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Data</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('monitoring.chart') }}" class="row g-3">
            <div class="col-md-6">
                <label for="device_id" class="form-label">Ruangan/Device</label>
                <select name="device_id" id="device_id" class="form-select">
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" {{ $selectedDevice == $device->id ? 'selected' : '' }}>
                            {{ $device->device_name }} ({{ $device->location }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="timeframe" class="form-label">Rentang Waktu</label>
                <select name="timeframe" id="timeframe" class="form-select">
                    <option value="10_min" {{ $timeframe == '10_min' ? 'selected' : '' }}>10 Menit Terakhir</option>
                    <option value="30_min" {{ $timeframe == '30_min' ? 'selected' : '' }}>30 Menit Terakhir</option>
                    <option value="1_hour" {{ $timeframe == '1_hour' ? 'selected' : '' }}>1 Jam Terakhir</option>
                    <option value="6_hours" {{ $timeframe == '6_hours' ? 'selected' : '' }}>6 Jam Terakhir</option>
                    <option value="12_hours" {{ $timeframe == '12_hours' ? 'selected' : '' }}>12 Jam Terakhir</option>
                    <option value="1_day" {{ $timeframe == '1_day' ? 'selected' : '' }}>1 Hari Terakhir</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-refresh"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Time Range Info -->
@php
    $timeframeLabels = [
        '10_min' => '10 Menit Terakhir',
        '30_min' => '30 Menit Terakhir',
        '1_hour' => '1 Jam Terakhir',
        '6_hours' => '6 Jam Terakhir',
        '12_hours' => '12 Jam Terakhir',
        '1_day' => '1 Hari Terakhir'
    ];
    $displayTimeframe = $timeframeLabels[$timeframe] ?? '1 Hari Terakhir';
@endphp
<div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
    <i class="fas fa-clock me-2"></i>
    <strong>Rentang Waktu:</strong> {{ $displayTimeframe }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

<!-- Statistics Cards - Professional Style -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <small class="text-muted d-block fw-bold">Rata-rata Suhu</small>
                        <h3 class="mb-0" style="color: #0056b3;" id="tempAvg">-°C</h3>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-thermometer-half fa-3x" style="color: #0056b3; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <small class="text-muted d-block fw-bold">Rata-rata Kelembapan</small>
                        <h3 class="mb-0" style="color: #007bff;" id="humAvg">-%</h3>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-tint fa-3x" style="color: #007bff; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Charts - Professional ApexCharts Style -->
@if(!empty($chartData['temperatures']) && count($chartData['temperatures']) > 0)

    <!-- Overlay Chart: Temperature & Humidity -->
    <div class="card mb-4">
        <div class="card-header" style="background: linear-gradient(135deg, #0056b3 0%, #004084 100%); color: white;">
            <h5 class="mb-0" style="color: white;"><i class="fas fa-wave-square"></i> Grafik Suhu & Kelembapan Overlay</h5>
        </div>
        <div class="card-body" style="background-color: #f8f9fa;">
            <div id="overlayChart" style="height: 400px;"></div>
        </div>
    </div>
@else
    <div class="card mb-4">
        <div class="card-body">
            <div class="alert alert-warning mb-3">
                <i class="fas fa-exclamation-circle"></i> <strong>Tidak Ada Data</strong><br>
                Tidak ditemukan data monitoring untuk device <strong>{{ $selectedDevice }}</strong> dalam rentang <strong>{{ $timeframeLabels[$timeframe] ?? $timeframe }}</strong>.
            </div>
            <div class="alert alert-info">
                <strong>💡 Saran:</strong> 
                <ul class="mb-0">
                    <li>Coba pilih rentang waktu yang lebih panjang (mis: <strong>1 Hari Terakhir</strong>)</li>
                    <li>Pastikan ESP8266 sedang mengirim data</li>
                    <li>Periksa device yang dipilih sudah benar</li>
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- Status Alert [REMOVED - Professional layouts don't need this] -->

<!-- Reference -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-book"></i> Standar Kondisi Normal Bayi</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info mb-3">
            <strong>Suhu Tubuh Bayi:</strong> 36.5°C - 37.5°C (Zona Aman) | <strong>Kelembapan:</strong> 40% - 60%
        </div>
        <div class="alert alert-warning mb-0">
            <strong>⚠ Peringatan:</strong> Jika suhu < 36.5°C (hipotermia) atau > 37.5°C (demam), segera lakukan pengecekan
        </div>
    </div>
</div>

@endsection

@section('js')
<!-- ApexCharts Library - Professional Financial Charts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chartData = @json($chartData);
        
        if (!chartData || !chartData.temperatures || chartData.temperatures.length === 0) {
            console.log('No data available for charts');
            return;
        }

        // Prepare data for charts
        const temperatures = chartData.temperatures || [];
        const humidities = chartData.humidities || [];
        const dates = chartData.dates || [];

        // Filter out zero/null values for better visualization
        const validIndices = [];
        for (let i = 0; i < temperatures.length; i++) {
            if ((temperatures[i] !== 0 && temperatures[i] !== null) || (humidities[i] !== 0 && humidities[i] !== null)) {
                validIndices.push(i);
            }
        }

        let filteredTemps = temperatures;
        let filteredHum = humidities;
        let filteredDates = dates;

        if (validIndices.length > 0) {
            filteredTemps = validIndices.map(i => temperatures[i]);
            filteredHum = validIndices.map(i => humidities[i]);
            filteredDates = validIndices.map(i => dates[i]);
        }

        // Prepare candlestick data (Open, High, Low, Close)
        const candleData = filteredTemps.map((temp, idx) => ({
            x: new Date(filteredDates[idx]),
            y: [temp, temp + 0.5, temp - 0.5, temp]  // Simple OHLC: O=temp, H=temp+0.5, L=temp-0.5, C=temp
        }));

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
                background: '#1a1a1a'
            },
            title: {
                text: 'Temperature Candlestick Analysis',
                style: {
                    color: '#fff',
                    fontSize: '14px'
                }
            },
            xaxis: {
                type: 'datetime',
                labels: {
                    style: { colors: '#888', fontSize: '11px' }
                },
                axisBorder: { color: '#444' },
                axisTicks: { color: '#444' }
            },
            yaxis: {
                title: {
                    text: 'Temperature (°C)',
                    style: { color: '#0056b3', fontSize: '12px', fontWeight: 'bold' }
                },
                labels: {
                    style: { colors: '#0056b3', fontSize: '11px' }
                }
            },
            plotOptions: {
                candlestick: {
                    colors: {
                        upward: '#0056b3',
                        downward: '#e8f0ff'
                    },
                    wick: {
                        useFillColor: true
                    }
                }
            },
            grid: {
                borderColor: '#333',
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

        // Render Candlestick Chart
        const candleChart = new ApexCharts(document.querySelector('#candleChart'), {
            ...candleOptions,
            series: [{
                data: candleData
            }]
        });
        candleChart.render();

        // Chart 2: Area Chart with dual axis (Overlay)
        const labelsForOverlay = filteredDates.map(date => {
            const d = new Date(date);
            return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        });

        const overlayOptions = {
            chart: {
                type: 'area',
                height: 400,
                zoom: { enabled: true },
                toolbar: { show: true },
                background: '#f8f9fa'
            },
            colors: ['#0056b3', '#007bff'],
            dataLabels: { enabled: false },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                }
            },
            xaxis: {
                categories: labelsForOverlay,
                labels: {
                    style: { fontSize: '11px' }
                }
            },
            yaxis: [
                {
                    title: {
                        text: 'Temperature (°C)',
                        style: { color: '#0056b3', fontSize: '12px', fontWeight: 'bold' }
                    },
                    labels: {
                        style: { color: '#0056b3' }
                    }
                },
                {
                    opposite: true,
                    title: {
                        text: 'Humidity (%)',
                        style: { color: '#007bff', fontSize: '12px', fontWeight: 'bold' }
                    },
                    labels: {
                        style: { color: '#007bff' }
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
                borderColor: '#e0e0e0',
                strokeDashArray: 4
            }
        };

        // Render Overlay Chart
        const overlayChart = new ApexCharts(document.querySelector('#overlayChart'), {
            ...overlayOptions,
            series: [
                {
                    name: 'Temperature (°C)',
                    data: filteredTemps
                },
                {
                    name: 'Humidity (%)',
                    data: filteredHum
                }
            ]
        });
        overlayChart.render();

        // Calculate and display statistics
        const calculateStats = (data) => {
            const validData = data.filter(d => d !== null && d !== 0 && d !== undefined);
            if (validData.length === 0) return { avg: '-', max: '-', min: '-' };
            const avg = (validData.reduce((a, b) => a + b, 0) / validData.length).toFixed(1);
            const max = Math.max(...validData).toFixed(1);
            const min = Math.min(...validData).toFixed(1);
            return { avg, max, min };
        };

        const tempStats = calculateStats(filteredTemps);
        const humStats = calculateStats(filteredHum);

        // Update statistics display
        document.getElementById('tempAvg').textContent = tempStats.avg + '°C';
        document.getElementById('humAvg').textContent = humStats.avg + '%';

        console.log('✅ ApexCharts rendered successfully - Professional style!');
    });
</script>

<style>
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
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
