@extends('layouts.main')

@section('title', 'Grafik Monitoring - Sistem Monitoring Suhu Bayi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0"><i class="fas fa-chart-area"></i> Grafik Monitoring</h1>
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
                <label for="device_id" class="form-label">Device</label>
                <select name="device_id" id="device_id" class="form-select">
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" {{ $selectedDevice == $device->id ? 'selected' : '' }}>
                            {{ $device->device_name }} ({{ $device->location }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="days" class="form-label">Periode (Hari)</label>
                <select name="days" id="days" class="form-select">
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="14" {{ $days == 14 ? 'selected' : '' }}>14 Hari Terakhir</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="60" {{ $days == 60 ? 'selected' : '' }}>60 Hari Terakhir</option>
                </select>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-refresh"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-thermometer-half"></i> Grafik Suhu</h5>
            </div>
            <div class="card-body">
                <canvas id="temperatureChart" height="80"></canvas>
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <small class="text-muted">Rata-rata</small>
                        <h5 id="tempAvg">-</h5>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Maksimal</small>
                        <h5 id="tempMax">-</h5>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Minimal</small>
                        <h5 id="tempMin">-</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tint"></i> Grafik Kelembapan</h5>
            </div>
            <div class="card-body">
                <canvas id="humidityChart" height="80"></canvas>
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <small class="text-muted">Rata-rata</small>
                        <h5 id="humAvg">-</h5>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Maksimal</small>
                        <h5 id="humMax">-</h5>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Minimal</small>
                        <h5 id="humMin">-</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Combined Chart -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Grafik Kombinasi</h5>
    </div>
    <div class="card-body">
        <canvas id="combinedChart" height="50"></canvas>
    </div>
</div>

<!-- Reference -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Standar Kondisi</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-thermometer-half"></i> Suhu Normal</h6>
                <p class="mb-0"><strong>15°C - 30°C</strong></p>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-tint"></i> Kelembapan Normal</h6>
                <p class="mb-0"><strong>35% - 60%</strong></p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
    const chartData = @json($chartData);

    // Calculate statistics
    function calculateStats(data) {
        if (data.length === 0) return { avg: 0, max: 0, min: 0 };
        const avg = (data.reduce((a, b) => a + b, 0) / data.length).toFixed(2);
        const max = Math.max(...data).toFixed(2);
        const min = Math.min(...data).toFixed(2);
        return { avg, max, min };
    }

    // Temperature Statistics
    const tempStats = calculateStats(chartData.temperatures);
    document.getElementById('tempAvg').textContent = tempStats.avg + '°C';
    document.getElementById('tempMax').textContent = tempStats.max + '°C';
    document.getElementById('tempMin').textContent = tempStats.min + '°C';

    // Humidity Statistics
    const humStats = calculateStats(chartData.humidities);
    document.getElementById('humAvg').textContent = humStats.avg + '%';
    document.getElementById('humMax').textContent = humStats.max + '%';
    document.getElementById('humMin').textContent = humStats.min + '%';

    // Temperature Chart
    const tempCtx = document.getElementById('temperatureChart').getContext('2d');
    new Chart(tempCtx, {
        type: 'line',
        data: {
            labels: chartData.dates,
            datasets: [{
                label: 'Suhu (°C)',
                data: chartData.temperatures,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: 'rgb(255, 99, 132)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 40,
                    min: 10
                }
            }
        }
    });

    // Humidity Chart
    const humCtx = document.getElementById('humidityChart').getContext('2d');
    new Chart(humCtx, {
        type: 'line',
        data: {
            labels: chartData.dates,
            datasets: [{
                label: 'Kelembapan (%)',
                data: chartData.humidities,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: 'rgb(54, 162, 235)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    min: 0
                }
            }
        }
    });

    // Combined Chart
    const combCtx = document.getElementById('combinedChart').getContext('2d');
    new Chart(combCtx, {
        type: 'line',
        data: {
            labels: chartData.dates,
            datasets: [
                {
                    label: 'Suhu (°C)',
                    data: chartData.temperatures,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.05)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    yAxisID: 'y',
                },
                {
                    label: 'Kelembapan (%)',
                    data: chartData.humidities,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.05)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Suhu (°C)'
                    },
                    min: 10,
                    max: 40
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Kelembapan (%)'
                    },
                    min: 0,
                    max: 100,
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
</script>
@endsection
