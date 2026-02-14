<!-- HOURLY TREND CHART - Script hanya untuk OPSI 1 (Real-time Murni) -->
<!-- Gunakan endpoint: /api/monitoring/hourly-chart/dynamic -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest/dist/apexcharts.min.js"></script>

<script>
    // ========== OPSI 1: REAL-TIME MURNI (DIREKOMENDASIKAN) ==========
    // Grafik hanya menampilkan jam yang benar-benar ada data
    // Tidak ada label kosong dari 00:00 jika data mulai jam 10:57
    
    const selectedDevice = "{{ $selectedDevice }}";
    const selectedDate = "{{ $date }}";
    let hourlyChart = null;
    let pollInterval = null;
    
    // Build chart options dengan dynamic labels
    function buildDynamicChartOptions(chartData) {
        return {
            chart: {
                type: 'line',
                height: 500,
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
                categories: chartData.labels,  // ✅ DYNAMIC LABELS - HANYA JAM YANG ADA DATA
                labels: {
                    style: {
                        fontSize: '12px'
                    },
                    rotateAlways: false,
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
        if (hourlyChart) {
            hourlyChart.updateSeries([
                {
                    name: 'Rata-rata Suhu',
                    data: newChartData.avg_temperatures
                },
                {
                    name: 'Kelembapan',
                    data: newChartData.avg_humidities
                },
                {
                    name: 'Suhu Max',
                    data: newChartData.max_temperatures
                },
                {
                    name: 'Suhu Min',
                    data: newChartData.min_temperatures
                }
            ], false);
        }
    }
    
    // Fetch latest hourly data from API (OPSI 1: Dynamic)
    function fetchLatestHourlyData() {
        fetch(`/api/monitoring/hourly-chart/dynamic?device_id=${selectedDevice}&date=${selectedDate}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.data_count > 0) {
                    updateChart(data.data);
                    console.log(`✅ Chart updated at ${new Date().toLocaleTimeString()}`);
                    console.log(`📊 Data range: ${data.data.first_data_time} → ${data.data.last_data_time}`);
                    console.log(`📈 Data points: ${data.data.data_count} jam (OPSI 1: Real-time murni)`);
                }
            })
            .catch(error => {
                console.error('❌ Error fetching chart data:', error);
            });
    }
    
    // Initialize chart on page load
    document.addEventListener('DOMContentLoaded', function() {
        const initialChartData = @json($chartData);
        
        if (initialChartData.avg_temperatures && initialChartData.avg_temperatures.length > 0) {
            // Build options dengan data dinamis
            const chartOptions = buildDynamicChartOptions(initialChartData);
            
            // Create series
            chartOptions.series = [
                {
                    name: 'Rata-rata Suhu',
                    data: initialChartData.avg_temperatures
                },
                {
                    name: 'Kelembapan',
                    data: initialChartData.avg_humidities
                },
                {
                    name: 'Suhu Max',
                    data: initialChartData.max_temperatures
                },
                {
                    name: 'Suhu Min',
                    data: initialChartData.min_temperatures
                }
            ];
            
            // Initialize chart
            hourlyChart = new ApexCharts(document.querySelector("#hourlyChart"), chartOptions);
            hourlyChart.render();
            
            const firstHour = initialChartData.hours?.[0] || '?';
            const lastHour = initialChartData.hours?.[initialChartData.hours?.length - 1] || '?';
            console.log('📊 Hourly trend chart initialized (OPSI 1: Real-time murni)');
            console.log(`🎯 Data range: Jam ${firstHour}:00 - Jam ${lastHour}:00`);
            console.log(`📝 Labels: ${initialChartData.labels.join(', ')}`);
        }
        
        // Start polling untuk data terbaru setiap 10 detik
        pollInterval = setInterval(fetchLatestHourlyData, 10000);
        console.log('🔄 Real-time polling started (every 10 seconds, endpoint: /dynamic)');
    });
    
    // Cleanup when page unloads
    window.addEventListener('beforeunload', function() {
        if (pollInterval) {
            clearInterval(pollInterval);
            console.log('🛑 Polling stopped');
        }
    });
</script>
