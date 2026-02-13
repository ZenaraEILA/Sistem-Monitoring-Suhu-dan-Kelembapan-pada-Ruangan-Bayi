@extends('layouts.main')

@section('title', 'Grafik Monitoring - Sistem Monitoring Suhu Bayi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0"><i class="fas fa-chart-area"></i> Grafik Monitoring Interaktif</h1>
        <small class="text-muted">Gunakan scroll untuk zoom, drag untuk pan. Klik tombol reset untuk kembali ke tampilan awal.</small>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Data</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('monitoring.chart') }}" class="row g-3">
            <div class="col-md-4">
                <label for="device_id" class="form-label">Device</label>
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
            <div class="col-md-4">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-refresh"></i> Perbarui
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Chart Controls -->
<div class="card mb-3">
    <div class="card-body p-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" id="resetChart">
            <i class="fas fa-undo"></i> Reset Zoom
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="downloadChart">
            <i class="fas fa-download"></i> Download
        </button>
    </div>
</div>

<!-- Main Chart -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Grafik Suhu & Kelembapan</h5>
    </div>
    <div class="card-body">
        @if(empty($chartData['temperatures']) || count($chartData['temperatures']) === 0)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> <strong>Data Tidak Ditemukan</strong><br>
                Tidak ada data monitoring untuk device ini dalam periode yang dipilih. 
                Pastikan:
                <ul class="mb-0 mt-2">
                    <li>Device sudah terhubung dengan baik</li>
                    <li>Data telah dikirim dari ESP8266</li>
                    <li>Rentang waktu sudah sesuai</li>
                </ul>
            </div>
            <div class="text-center py-5">
                <p class="text-muted">Menunggu data dari sensor...</p>
            </div>
        @else
            <div id="mainChart"></div>
        @endif
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-danger bg-opacity-10">
                <h5 class="mb-0"><i class="fas fa-thermometer-half"></i> Statistik Suhu</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <small class="text-muted">Rata-rata</small>
                        <h5 id="tempAvg" class="text-danger">-</h5>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Maksimal</small>
                        <h5 id="tempMax" class="text-danger">-</h5>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Minimal</small>
                        <h5 id="tempMin" class="text-danger">-</h5>
                    </div>
                </div>
                <div class="mt-3" id="tempStatus"></div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-info bg-opacity-10">
                <h5 class="mb-0"><i class="fas fa-tint"></i> Statistik Kelembapan</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <small class="text-muted">Rata-rata</small>
                        <h5 id="humAvg" class="text-info">-</h5>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Maksimal</small>
                        <h5 id="humMax" class="text-info">-</h5>
                    </div>
                    <div class="col-4">
                        <small class="text-muted">Minimal</small>
                        <h5 id="humMin" class="text-info">-</h5>
                    </div>
                </div>
                <div class="mt-3" id="humStatus"></div>
            </div>
        </div>
    </div>
</div>

<!-- Alert Box -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Status</h5>
    </div>
    <div class="card-body" id="statusAlert">
        <span class="badge bg-success">✓ Semua Parameter Normal</span>
    </div>
</div>

<!-- Reference -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Standar Kondisi Normal</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-thermometer-half"></i> Suhu</h6>
                <p class="mb-0"><strong>15°C - 30°C</strong></p>
                <small class="text-muted">Zona Aman untuk Bayi Baru Lahir</small>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-tint"></i> Kelembapan</h6>
                <p class="mb-0"><strong>35% - 60%</strong></p>
                <small class="text-muted">Zona Aman untuk Kenyamanan Bayi</small>
            </div>
        </div>
        <hr>
        <div class="row small">
            <div class="col-md-4">
                <div class="d-flex align-items-center mb-2">
                    <div style="width: 10px; height: 10px; background: #dc3545; border-radius: 2px; margin-right: 8px;"></div>
                    <span>Suhu Tidak Aman</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center mb-2">
                    <div style="width: 10px; height: 10px; background: #ffc107; border-radius: 2px; margin-right: 8px;"></div>
                    <span>Peringatan</span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center mb-2">
                    <div style="width: 10px; height: 10px; background: #28a745; border-radius: 2px; margin-right: 8px;"></div>
                    <span>Normal</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<!-- ApexCharts Library -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>

<script>
    const chartData = @json($chartData);
    
    // Check if data exists
    if (!chartData.temperatures || chartData.temperatures.length === 0) {
        console.warn('No chart data available');
        document.getElementById('mainChart').style.display = 'none';
    } else {
        // Prepare data with timestamps
        const dataPoints = chartData.dates.map((date, index) => ({
            x: chartData.timestamps[index],
            temp: chartData.temperatures[index],
            humidity: chartData.humidities[index],
            status: chartData.statuses[index],
            time: date
        })).sort((a, b) => a.x - b.x);

        // Calculate Statistics
        function calculateStats(data) {
            if (data.length === 0) return { avg: 0, max: 0, min: 0 };
            const avg = (data.reduce((a, b) => a + b, 0) / data.length).toFixed(2);
            const max = Math.max(...data).toFixed(2);
            const min = Math.min(...data).toFixed(2);
            return { avg, max, min };
        }

        const tempStats = calculateStats(chartData.temperatures);
        const humStats = calculateStats(chartData.humidities);

        // Update stats display
        document.getElementById('tempAvg').textContent = tempStats.avg + '°C';
        document.getElementById('tempMax').textContent = tempStats.max + '°C';
        document.getElementById('tempMin').textContent = tempStats.min + '°C';
        document.getElementById('humAvg').textContent = humStats.avg + '%';
        document.getElementById('humMax').textContent = humStats.max + '%';
        document.getElementById('humMin').textContent = humStats.min + '%';

        // Check status
        const isTempSafe = tempStats.avg >= 15 && tempStats.avg <= 30;
        const isHumSafe = humStats.avg >= 35 && humStats.avg <= 60;
        
        const tempStatusEl = document.getElementById('tempStatus');
        tempStatusEl.innerHTML = isTempSafe ? 
            '<span class="badge bg-success">✓ Suhu Aman</span>' :
            '<span class="badge bg-danger">✗ Suhu Tidak Aman</span>';
        
        const humStatusEl = document.getElementById('humStatus');
        humStatusEl.innerHTML = isHumSafe ?
            '<span class="badge bg-success">✓ Kelembapan Aman</span>' :
            '<span class="badge bg-danger">✗ Kelembapan Tidak Aman</span>';

        const statusAlertEl = document.getElementById('statusAlert');
        if (isTempSafe && isHumSafe) {
            statusAlertEl.innerHTML = '<span class="badge bg-success">✓ Semua Parameter Normal</span>';
        } else {
            const alerts = [];
            if (!isTempSafe) alerts.push('<span class="badge bg-danger me-2">⚠ Suhu Abnormal</span>');
            if (!isHumSafe) alerts.push('<span class="badge bg-danger">⚠ Kelembapan Abnormal</span>');
            statusAlertEl.innerHTML = alerts.join('');
        }

        // Prepare ApexCharts Data
        const tempSeries = [{
            name: 'Suhu (°C)',
            data: dataPoints.map(p => ({ x: p.x, y: p.temp }))
        }];

    const humSeries = [{
        name: 'Kelembapan (%)',
        data: dataPoints.map(p => ({ x: p.x, y: p.humidity }))
    }];

    // Add incident markers
    const annotations = {};
    if (chartData.incidents && chartData.incidents.length > 0) {
        annotations.points = chartData.incidents.map(incident => ({
            x: incident.x,
            label: {
                borderColor: '#FF5733',
                offsetY: -10,
                style: {
                    color: '#fff',
                    background: '#FF5733',
                    fontSize: '10px'
                },
                text: incident.label
            }
        }));
    }

    // Main ApexCharts Configuration
    const options = {
        chart: {
            type: 'line',
            height: 450,
            stacked: false,
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
            width: [2.5, 2.5],
            dashArray: [0, 0]
        },
        plotOptions: {
            bar: {
                horizontal: false
            }
        },
        fill: {
            opacity: [0.15, 0.15],
            type: ['gradient', 'gradient']
        },
        labels: dataPoints.map(p => p.x),
        xaxis: {
            type: 'datetime',
            labels: {
                format: 'HH:mm:ss',
                style: {
                    fontSize: '11px'
                }
            },
            axisBorder: {
                show: true
            },
            axisTicks: {
                show: true
            }
        },
        yaxis: [
            {
                seriesName: 'Suhu (°C)',
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
                        fontSize: '11px'
                    },
                    formatter: function(value) {
                        return value.toFixed(1) + '°';
                    }
                },
                title: {
                    text: "Suhu (°C)",
                    style: {
                        color: '#dc3545',
                        fontSize: '12px',
                        fontWeight: 'bold'
                    }
                },
                plotBands: [
                    {
                        from: -50,
                        to: 15,
                        color: 'rgba(220, 53, 69, 0.08)',
                        label: {
                            borderColor: '#dc3545',
                            style: {
                                color: '#fff',
                                background: '#dc3545'
                            },
                            text: 'Terlalu Dingin'
                        }
                    },
                    {
                        from: 30,
                        to: 50,
                        color: 'rgba(220, 53, 69, 0.08)',
                        label: {
                            borderColor: '#dc3545',
                            style: {
                                color: '#fff',
                                background: '#dc3545'
                            },
                            text: 'Terlalu Panas'
                        }
                    }
                ],
                min: 10,
                max: 40,
                tooltip: {
                    enabled: true
                }
            },
            {
                seriesName: 'Kelembapan (%)',
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
                        fontSize: '11px'
                    },
                    formatter: function(value) {
                        return value.toFixed(0) + '%';
                    }
                },
                title: {
                    text: "Kelembapan (%)",
                    style: {
                        color: '#0dcaf0',
                        fontSize: '12px',
                        fontWeight: 'bold'
                    }
                },
                plotBands: [
                    {
                        from: 0,
                        to: 35,
                        color: 'rgba(13, 202, 240, 0.08)',
                        label: {
                            borderColor: '#0dcaf0',
                            style: {
                                color: '#fff',
                                background: '#0dcaf0'
                            },
                            text: 'Terlalu Kering'
                        }
                    },
                    {
                        from: 60,
                        to: 100,
                        color: 'rgba(13, 202, 240, 0.08)',
                        label: {
                            borderColor: '#0dcaf0',
                            style: {
                                color: '#fff',
                                background: '#0dcaf0'
                            },
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
            floating: true,
            offsetY: -25,
            offsetX: -5,
            fontSize: '12px',
            fontWeight: 'bold'
        },
        colors: ['#dc3545', '#0dcaf0'],
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
        },
        annotations: annotations
    };

    // Combine data for single chart
    const combinedSeries = [
        {
            name: 'Suhu (°C)',
            data: dataPoints.map(p => ({ x: p.x, y: p.temp }))
        },
        {
            name: 'Kelembapan (%)',
            data: dataPoints.map(p => ({ x: p.x, y: p.humidity }))
        }
    ];

    options.series = combinedSeries;
    
    // Initialize Chart
    const chart = new ApexCharts(document.querySelector("#mainChart"), options);
    chart.render();

    // Reset Button
    document.getElementById('resetChart').addEventListener('click', function() {
        chart.resetSeries();
        chart.zoomX(dataPoints[0].x, dataPoints[dataPoints.length - 1].x);
    });

    // Download Button
    document.getElementById('downloadChart').addEventListener('click', function() {
        chart.dataURI().then(({ imgURI, blob }) => {
            const link = document.createElement('a');
            link.href = imgURI;
            link.download = 'monitoring-chart-' + new Date().toISOString().slice(0, 10) + '.png';
            link.click();
        });
    });
    } // Close if data exists
</script>
@endsection
