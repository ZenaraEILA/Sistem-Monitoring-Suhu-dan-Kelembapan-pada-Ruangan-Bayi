@extends('layouts.main')

@section('title', 'Tren Harian - Sistem Monitoring')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3"><i class="fas fa-chart-line"></i> Grafik Tren Harian</h1>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('monitoring.hourly-trend') }}" method="get" class="row g-3">
            <div class="col-md-6">
                <label for="device_id" class="form-label">Pilih Ruangan</label>
                <select name="device_id" id="device_id" class="form-select">
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" {{ $device->id == $selectedDevice ? 'selected' : '' }}>
                            {{ $device->device_name }} - {{ $device->location }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="date" class="form-label">Pilih Tanggal</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Tampilkan Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statistics -->
@if(count($hourlyData) > 0)
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <small class="text-muted">Rata-rata Suhu</small>
                <div class="h3 text-primary mb-0">{{ round(collect($chartData['avg_temperatures'])->avg(), 2) }}°C</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <small class="text-muted">Suhu Maksimal</small>
                <div class="h3 text-danger mb-0">{{ max($chartData['max_temperatures']) ?? 0 }}°C</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <small class="text-muted">Suhu Minimal</small>
                <div class="h3 text-info mb-0">{{ min($chartData['min_temperatures']) ?? 0 }}°C</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <small class="text-muted">Total Data</small>
                <div class="h3 text-success mb-0">{{ count($hourlyData) }}</div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Charts -->
<div class="row">
    <div class="col-12">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Perubahan Suhu Per Jam</h5>
            </div>
            <div class="card-body">
                <canvas id="tempChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line"></i> Perubahan Kelembapan Per Jam</h5>
            </div>
            <div class="card-body">
                <canvas id="humidityChart" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Table -->
@if(count($hourlyData) > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table"></i> Data Detail Per Jam</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Jam</th>
                            <th>Rata-rata Suhu</th>
                            <th>Max/Min Suhu</th>
                            <th>Rata-rata Kelembapan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hourlyData as $data)
                        <tr>
                            <td><strong>{{ $data->hour }}:00 - {{ str_pad($data->hour + 1, 2, '0', STR_PAD_LEFT) }}:00</strong></td>
                            <td>{{ $data->avg_temp }}°C</td>
                            <td>
                                <span class="{{ $data->max_temp > 30 ? 'text-danger' : 'text-success' }}">{{ $data->max_temp }}°C</span> / 
                                <span class="{{ $data->min_temp < 15 ? 'text-danger' : 'text-success' }}">{{ $data->min_temp }}°C</span>
                            </td>
                            <td>{{ $data->avg_humidity }}%</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> Tidak ada data untuk tanggal yang dipilih
</div>
@endif

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Temperature Chart
        const tempCtx = document.getElementById('tempChart').getContext('2d');
        new Chart(tempCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['hours']) !!},
                datasets: [
                    {
                        label: 'Rata-rata Suhu',
                        data: {!! json_encode($chartData['avg_temperatures']) !!},
                        borderColor: '#FF6B6B',
                        backgroundColor: 'rgba(255, 107, 107, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#FF6B6B',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Suhu Maksimal',
                        data: {!! json_encode($chartData['max_temperatures']) !!},
                        borderColor: '#FFA500',
                        borderDash: [5, 5],
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Suhu Minimal',
                        data: {!! json_encode($chartData['min_temperatures']) !!},
                        borderColor: '#4169E1',
                        borderDash: [5, 5],
                        tension: 0.3,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
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
                            color: '#333'
                        },
                        min: 10,
                        max: 35
                    }
                }
            }
        });

        // Humidity Chart
        const humidityCtx = document.getElementById('humidityChart').getContext('2d');
        new Chart(humidityCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['hours']) !!},
                datasets: [
                    {
                        label: 'Kelembapan',
                        data: {!! json_encode($chartData['avg_humidities']) !!},
                        borderColor: '#4ECDC4',
                        backgroundColor: 'rgba(78, 205, 196, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#4ECDC4',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Kelembapan (%)',
                            color: '#333'
                        },
                        min: 0,
                        max: 100
                    }
                }
            }
        });
    });
</script>

<style>
    .card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
</style>
@endsection
