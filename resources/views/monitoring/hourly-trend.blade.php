@extends('layouts.main')

@section('title', 'Tren Harian - Sistem Monitoring')

@section('content')
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

<!-- Statistics -->
@if(count($hourlyData) > 0)
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <small class="text-muted d-block">Rata-rata Suhu</small>
                        <h4 class="mb-0" style="color: #dc3545;">
                            {{ round(collect($chartData['avg_temperatures'])->avg(), 1) }}°C
                        </h4>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-thermometer-half fa-2x" style="color: #dc3545; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <small class="text-muted d-block">Suhu Maksimal</small>
                        <h4 class="mb-0" style="color: #ff6b6b;">
                            {{ max($chartData['max_temperatures']) ?? 0 }}°C
                        </h4>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-arrow-up fa-2x" style="color: #ff6b6b; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <small class="text-muted d-block">Suhu Minimal</small>
                        <h4 class="mb-0" style="color: #0dcaf0;">
                            {{ min($chartData['min_temperatures']) ?? 0 }}°C
                        </h4>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-arrow-down fa-2x" style="color: #0dcaf0; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <small class="text-muted d-block">Total Data Per Jam</small>
                        <h4 class="mb-0" style="color: #28a745;">
                            {{ count($hourlyData) }}/24
                        </h4>
                    </div>
                    <div class="ms-auto">
                        <i class="fas fa-clock fa-2x" style="color: #28a745; opacity: 0.2;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

    <!-- Charts -->
@if(count($hourlyData) > 0)
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Grafik Perubahan Suhu & Kelembapan Per Jam</h5>
    </div>
    <div class="card-body">
        <div id="hourlyChart" style="height: 500px;"></div>
    </div>
</div>
@endif

<!-- Detailed Table -->
@if(count($hourlyData) > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-table"></i> Data Detail Per Jam</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 10%;">Jam</th>
                    <th class="text-center" style="width: 22.5%;">
                        <i class="fas fa-thermometer-half" style="color: #dc3545;"></i> Suhu (°C)
                    </th>
                    <th class="text-center" style="width: 22.5%;">
                        <i class="fas fa-tint" style="color: #0dcaf0;"></i> Kelembapan (%)
                    </th>
                    <th class="text-center" style="width: 45%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($hourlyData as $data)
                @php
                    // Determine temperature status
                    $tempStatus = 'Normal';
                    $tempStatusClass = 'success';
                    if ($data->max_temp > 30 || $data->min_temp < 15) {
                        $tempStatus = 'Tidak Aman';
                        $tempStatusClass = 'danger';
                    } elseif ($data->max_temp > 28 || $data->min_temp < 17) {
                        $tempStatus = 'Peringatan';
                        $tempStatusClass = 'warning';
                    }
                    
                    // Determine humidity status
                    $humStatus = 'Normal';
                    $humStatusClass = 'success';
                    if ($data->avg_humidity > 60 || $data->avg_humidity < 35) {
                        $humStatus = 'Tidak Aman';
                        $humStatusClass = 'danger';
                    } elseif ($data->avg_humidity > 55 || $data->avg_humidity < 40) {
                        $humStatus = 'Peringatan';
                        $humStatusClass = 'warning';
                    }
                @endphp
                <tr>
                    <td class="text-center fw-bold">
                        {{ str_pad($data->hour, 2, '0', STR_PAD_LEFT) }}:00
                    </td>
                    <td>
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <div class="text-center">
                                <small class="d-block text-muted">Rata-rata</small>
                                <span class="text-dark fw-bold">{{ number_format($data->avg_temp, 1) }}°</span>
                            </div>
                            <div class="text-center">
                                <small class="d-block text-muted">Max</small>
                                <span class="text-success fw-bold">{{ $data->max_temp }}°</span>
                            </div>
                            <div class="text-center">
                                <small class="d-block text-muted">Min</small>
                                <span class="text-info fw-bold">{{ $data->min_temp }}°</span>
                            </div>
                            <div>
                                <span class="badge bg-{{ $tempStatusClass }}">{{ $tempStatus }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex justify-content-center align-items-center gap-2">
                            <div class="text-center">
                                <small class="d-block text-muted">Rata-rata</small>
                                <span class="text-dark fw-bold">{{ number_format($data->avg_humidity, 0) }}%</span>
                            </div>
                            <div>
                                <span class="badge bg-{{ $humStatusClass }}">{{ $humStatus }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        @php
                            $overallStatus = ($tempStatus === 'Tidak Aman' || $humStatus === 'Tidak Aman') ? 'Tidak Aman' : 
                                            (($tempStatus === 'Peringatan' || $humStatus === 'Peringatan') ? 'Peringatan' : 'Aman');
                            $overallClass = ($overallStatus === 'Tidak Aman') ? 'danger' : 
                                           (($overallStatus === 'Peringatan') ? 'warning' : 'success');
                        @endphp
                        <span class="badge bg-{{ $overallClass }} p-2">
                            @if($overallStatus === 'Aman')
                                <i class="fas fa-check-circle me-1"></i> {{ $overallStatus }}
                            @elseif($overallStatus === 'Peringatan')
                                <i class="fas fa-exclamation-triangle me-1"></i> {{ $overallStatus }}
                            @else
                                <i class="fas fa-times-circle me-1"></i> {{ $overallStatus }}
                            @endif
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="alert alert-info" role="alert">
    <i class="fas fa-info-circle me-2"></i> <strong>Tidak ada data</strong> untuk tanggal dan device yang dipilih
</div>
@endif

@endsection

@section('js')
<!-- ApexCharts Library -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>

<script>
    // Initial chart data from server
    let currentChartData = @json($chartData);
    let hourlyChart = null;
    let pollInterval = null;
    
    // Get selected device and date from page
    const selectedDevice = "{{ $selectedDevice }}";
    const selectedDate = "{{ $date }}";
    
    // Function to expand hourly data to 10-minute intervals
    function expandDataTo10Minutes(hourlyArray) {
        const expanded = [];
        for (let i = 0; i < hourlyArray.length; i++) {
            expanded.push(hourlyArray[i]); // Add main point
            
            // Add intermediate points (6 points per hour)
            if (i < hourlyArray.length - 1) {
                const current = parseFloat(hourlyArray[i]);
                const next = parseFloat(hourlyArray[i + 1]);
                
                // Linear interpolation for 5 intermediate points
                for (let j = 1; j < 6; j++) {
                    const interpolated = current + (next - current) * (j / 6);
                    expanded.push(parseFloat(interpolated.toFixed(2)));
                }
            } else {
                // For last hour, add intermediate points with same value
                for (let j = 1; j < 6; j++) {
                    expanded.push(hourlyArray[i]);
                }
            }
        }
        return expanded;
    }
    
    // Function to generate 10-minute labels for 24 hours
    function generate10MinuteLabels() {
        const labels = [];
        for (let hour = 0; hour < 24; hour++) {
            for (let minute = 0; minute < 60; minute += 10) {
                const hourStr = String(hour).padStart(2, '0');
                const minStr = String(minute).padStart(2, '0');
                labels.push(`${hourStr}:${minStr}`);
            }
        }
        return labels;
    }
    
    // Function to build chart options
    function buildChartOptions() {
        const labels10Min = generate10MinuteLabels();
        
        return {
            chart: {
                type: 'line',
                height: 500,
                stacked: false,
                animations: {
                    enabled: true,
                    easing: 'linear',
                    speed: 800,
                    animateGradually: {
                        enabled: true,
                        delay: 150
                    },
                    dynamicAnimation: {
                        enabled: true,
                        speed: 150
                    }
                },
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true,
                    }
                },
                zoom: {
                    enabled: true,
                    type: 'xy'
                }
            },
            stroke: {
                curve: 'smooth',
                width: [3, 3, 2, 2],
                dashArray: [0, 0, 5, 5]
            },
            plotOptions: {
                bar: {
                    horizontal: false
                }
            },
            fill: {
                opacity: [0.15, 0.15, 0, 0],
                type: ['gradient', 'gradient', 'solid', 'solid']
            },
            xaxis: {
                categories: labels10Min,
                labels: {
                    style: {
                        fontSize: '10px'
                    },
                    rotateAlways: true,
                    rotate: 45,
                    hideOverlappingLabels: true,
                    showDuplicates: false
                },
                axisBorder: {
                    show: true
                },
                tickPlacement: 'on'
            },
            yaxis: [
                {
                    seriesName: 'Rata-rata Suhu',
                    axisTicks: {
                        show: true,
                    },
                    axisBorder: {
                        show: true,
                        color: '#dc3545'
                    },
                    labels: {
                        style: {
                            colors: '#dc3545',
                            fontSize: '12px'
                        },
                        formatter: function(value) {
                            return value.toFixed(1) + '°';
                        }
                    },
                    title: {
                        text: "Suhu (°C)",
                        style: {
                            color: '#dc3545',
                            fontSize: '13px',
                            fontWeight: 'bold'
                        }
                    },
                    plotBands: [
                        {
                            from: -50,
                            to: 15,
                            color: 'rgba(220, 53, 69, 0.08)',
                            label: {
                                text: 'Terlalu Dingin'
                            }
                        },
                        {
                            from: 30,
                            to: 50,
                            color: 'rgba(220, 53, 69, 0.08)',
                            label: {
                                text: 'Terlalu Panas'
                            }
                        }
                    ],
                    min: 12,
                    max: 50,
                    tooltip: {
                        enabled: true
                    }
                },
                {
                    seriesName: 'Kelembapan',
                    opposite: true,
                    axisTicks: {
                        show: true,
                    },
                    axisBorder: {
                        show: true,
                        color: '#0dcaf0'
                    },
                    labels: {
                        style: {
                            colors: '#0dcaf0',
                            fontSize: '12px'
                        },
                        formatter: function(value) {
                            return value.toFixed(0) + '%';
                        }
                    },
                    title: {
                        text: "Kelembapan (%)",
                        style: {
                            color: '#0dcaf0',
                            fontSize: '13px',
                            fontWeight: 'bold'
                        }
                    },
                    plotBands: [
                        {
                            from: 0,
                            to: 35,
                            color: 'rgba(13, 202, 240, 0.08)',
                            label: {
                                text: 'Terlalu Kering'
                            }
                        },
                        {
                            from: 60,
                            to: 100,
                            color: 'rgba(13, 202, 240, 0.08)',
                            label: {
                                text: 'Terlalu Lembap'
                            }
                        }
                    ],
                    min: 0,
                    max: 100,
                    tooltip: {
                        enabled: true
                    }
                }
            ],
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function(y, opts) {
                        if (opts.seriesIndex === 0) {
                            return y.toFixed(1) + '°C';
                        } else if (opts.seriesIndex === 1) {
                            return y.toFixed(0) + '%';
                        } else if (opts.seriesIndex === 2) {
                            return y.toFixed(1) + '° (Max)';
                        } else if (opts.seriesIndex === 3) {
                            return y.toFixed(1) + '° (Min)';
                        }
                        return y;
                    }
                },
                theme: 'dark',
                style: {
                    fontSize: '12px'
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                floating: false,
                fontSize: '12px',
                fontWeight: 'bold'
            },
            colors: ['#dc3545', '#0dcaf0', '#ff7f00', '#17a2b8'],
            grid: {
                borderColor: 'rgba(0, 0, 0, 0.1)',
                strokeDashArray: 3,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            }
        };
    }
    
    // Function to update chart with new data
    function updateChart(newChartData) {
        currentChartData = newChartData;
        
        // Expand data to 10-minute intervals
        const expandedTemps = expandDataTo10Minutes(newChartData.avg_temperatures);
        const expandedHumidities = expandDataTo10Minutes(newChartData.avg_humidities);
        const expandedMaxTemps = expandDataTo10Minutes(newChartData.max_temperatures);
        const expandedMinTemps = expandDataTo10Minutes(newChartData.min_temperatures);
        
        // Update chart series
        if (hourlyChart) {
            hourlyChart.updateSeries([
                {
                    name: 'Rata-rata Suhu',
                    data: expandedTemps
                },
                {
                    name: 'Kelembapan',
                    data: expandedHumidities
                },
                {
                    name: 'Suhu Max',
                    data: expandedMaxTemps
                },
                {
                    name: 'Suhu Min',
                    data: expandedMinTemps
                }
            ], false);
        }
    }
    
    // Function to fetch latest hourly data from API
    function fetchLatestHourlyData() {
        fetch(`/api/monitoring/hourly-chart?device_id=${selectedDevice}&date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateChart(data.data);
                    console.log('✅ Chart updated at', new Date().toLocaleTimeString());
                }
            })
            .catch(error => {
                console.error('❌ Error fetching chart data:', error);
            });
    }
    
    // Initialize chart on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Build initial chart options and series
        const chartOptions = buildChartOptions();
        
        // Expand initial data
        const expandedTemps = expandDataTo10Minutes(currentChartData.avg_temperatures);
        const expandedHumidities = expandDataTo10Minutes(currentChartData.avg_humidities);
        const expandedMaxTemps = expandDataTo10Minutes(currentChartData.max_temperatures);
        const expandedMinTemps = expandDataTo10Minutes(currentChartData.min_temperatures);
        
        // Create series
        chartOptions.series = [
            {
                name: 'Rata-rata Suhu',
                data: expandedTemps
            },
            {
                name: 'Kelembapan',
                data: expandedHumidities
            },
            {
                name: 'Suhu Max',
                data: expandedMaxTemps
            },
            {
                name: 'Suhu Min',
                data: expandedMinTemps
            }
        ];
        
        // Initialize chart
        hourlyChart = new ApexCharts(document.querySelector("#hourlyChart"), chartOptions);
        hourlyChart.render();
        
        console.log('📊 Hourly trend chart initialized');
        
        // Start polling for new data every 10 seconds
        pollInterval = setInterval(fetchLatestHourlyData, 10000);
        console.log('🔄 Real-time polling started (every 10 seconds)');
    });
    
    // Cleanup when page unloads
    window.addEventListener('beforeunload', function() {
        if (pollInterval) {
            clearInterval(pollInterval);
            console.log('🛑 Polling stopped');
        }
    });
</script>

<style>
    .card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: none;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
</style>
@endsection
