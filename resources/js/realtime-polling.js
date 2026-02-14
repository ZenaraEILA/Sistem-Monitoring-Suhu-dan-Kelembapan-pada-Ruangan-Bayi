/**
 * REAL-TIME POLLING HELPER
 * File: resources/js/realtime-polling.js
 * 
 * Gunakan di blade template untuk real-time chart dan indicator update
 * Include: <script src="{{ asset('js/realtime-polling.js') }}"></script>
 * 
 * Atau copy-paste function-function ini langsung ke blade @section('js')
 */

class RealtimePoller {
    constructor(config = {}) {
        this.deviceId = config.deviceId || 1;
        this.indicatorInterval = config.indicatorInterval || 2000; // 2 detik
        this.chartInterval = config.chartInterval || 5000; // 5 detik
        this.timeframe = config.timeframe || '1_hour';
        this.chart = config.chart || null;
        
        this.indicatorTimerId = null;
        this.chartTimerId = null;
        
        this.debugMode = config.debugMode || false;
        this.baseUrl = config.baseUrl || '/api/monitoring';
        
        console.log('[RealtimePoller] Initialized with config:', config);
    }

    /**
     * Log untuk debugging
     */
    debug(message, data = null) {
        if (this.debugMode) {
            if (data) {
                console.log(`[RealtimePoller] ${message}`, data);
            } else {
                console.log(`[RealtimePoller] ${message}`);
            }
        }
    }

    /**
     * Update live indicators (suhu, kelembaban, status)
     * Dipanggil setiap 2 detik
     */
    updateIndicators() {
        const url = `${this.baseUrl}/get-latest?device_id=${this.deviceId}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(json => {
                if (!json.success) {
                    console.warn('[RealtimePoller] API gagal:', json.message);
                    return;
                }

                const data = json.data;
                this.debug('Indicator update', data);

                // Update temperature
                const tempElement = document.getElementById('latestTemperature');
                if (tempElement) {
                    tempElement.textContent = data.temperature.toFixed(1) + '°C';
                    tempElement.classList.remove('text-danger', 'text-warning', 'text-success');
                    if (data.temperature > 30 || data.temperature < 15) {
                        tempElement.classList.add('text-danger');
                    }
                }

                // Update humidity
                const humidityElement = document.getElementById('latestHumidity');
                if (humidityElement) {
                    humidityElement.textContent = data.humidity.toFixed(1) + '%';
                    humidityElement.classList.remove('text-danger', 'text-warning', 'text-success');
                    if (data.humidity > 60 || data.humidity < 35) {
                        humidityElement.classList.add('text-danger');
                    }
                }

                // Update status badge
                const statusBadge = document.getElementById('statusBadge');
                if (statusBadge) {
                    statusBadge.textContent = data.status;
                    statusBadge.classList.remove('badge-success', 'badge-danger', 'bg-success', 'bg-danger');
                    if (data.status === 'Aman') {
                        statusBadge.classList.add('badge-success', 'bg-success');
                    } else {
                        statusBadge.classList.add('badge-danger', 'bg-danger');
                    }
                }

                // Update last update time
                const lastUpdateElement = document.getElementById('lastUpdateTime');
                if (lastUpdateElement) {
                    lastUpdateElement.textContent = new Date().toLocaleTimeString();
                }
            })
            .catch(error => {
                console.error('[RealtimePoller] Indicator update error:', error);
            });
    }

    /**
     * Update chart data dengan ApexCharts
     * Dipanggil setiap 5 detik
     */
    updateChart() {
        if (!this.chart) {
            this.debug('Chart object not set, skipping update');
            return;
        }

        const url = `${this.baseUrl}/get-chart-data?device_id=${this.deviceId}&timeframe=${this.timeframe}`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(json => {
                if (!json.success) {
                    console.warn('[RealtimePoller] Chart API gagal:', json.message);
                    return;
                }

                const data = json.data;
                this.debug('Chart update', { count: data.count, timeframe: data.timeframe });

                if (data.count === 0) {
                    this.debug('No chart data available');
                    return;
                }

                // Prepare series untuk ApexCharts
                const newSeries = [
                    {
                        name: 'Suhu (°C)',
                        data: data.temperatures
                    },
                    {
                        name: 'Kelembaban (%)',
                        data: data.humidities
                    }
                ];

                try {
                    // Update series (lebih efisien daripada destroy + recreate)
                    this.chart.updateSeries(newSeries, false);
                    this.chart.updateOptions({
                        xaxis: {
                            categories: data.dates
                        }
                    }, false);
                    
                    this.debug('Chart updated successfully');
                } catch (error) {
                    console.error('[RealtimePoller] Chart update error:', error);
                }
            })
            .catch(error => {
                console.error('[RealtimePoller] Chart fetch error:', error);
            });
    }

    /**
     * Mulai polling real-time
     */
    start() {
        console.log('[RealtimePoller] Starting real-time polling...');
        
        // Immediate update sebelum interval pertama
        this.updateIndicators();
        this.updateChart();

        // Set interval untuk updates
        this.indicatorTimerId = setInterval(() => this.updateIndicators(), this.indicatorInterval);
        this.chartTimerId = setInterval(() => this.updateChart(), this.chartInterval);

        console.log('[RealtimePoller] ✅ Real-time polling started!');
        return this;
    }

    /**
     * Stop polling real-time
     */
    stop() {
        console.log('[RealtimePoller] Stopping real-time polling...');
        
        if (this.indicatorTimerId) {
            clearInterval(this.indicatorTimerId);
            this.indicatorTimerId = null;
        }
        
        if (this.chartTimerId) {
            clearInterval(this.chartTimerId);
            this.chartTimerId = null;
        }

        console.log('[RealtimePoller] ✅ Real-time polling stopped');
        return this;
    }

    /**
     * Pause polling (untuk saat tab tidak aktif)
     */
    pause() {
        this.debug('Pausing polling');
        this.stop();
    }

    /**
     * Resume polling
     */
    resume() {
        this.debug('Resuming polling');
        this.start();
    }

    /**
     * Setup automatic pause/resume saat tab visibility berubah
     */
    setupVisibilityHandler() {
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.debug('Tab is hidden, pausing');
                this.pause();
            } else {
                this.debug('Tab is visible, resuming');
                this.resume();
            }
        });
        
        console.log('[RealtimePoller] Visibility handler setup');
        return this;
    }

    /**
     * Setup cleanup saat page unload
     */
    setupUnloadHandler() {
        window.addEventListener('beforeunload', () => {
            this.stop();
        });
        
        console.log('[RealtimePoller] Unload handler setup');
        return this;
    }

    /**
     * Setup lengkap dengan semua handlers
     */
    setupAll() {
        this.start();
        this.setupVisibilityHandler();
        this.setupUnloadHandler();
        return this;
    }
}

// Export untuk modular usage (optional, jika pakai module bundler)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RealtimePoller;
}
