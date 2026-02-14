/**
 * Real-Time Device Status Monitor
 * Fitur:
 * - Polling setiap 1 detik untuk mendapatkan status device
 * - Hitung selisih waktu (seconds_ago) dari timestamp server
 * - Deteksi perubahan status (ONLINE → OFFLINE, dll)
 * - Trigger notifikasi saat ada perubahan status
 * - Update UI dengan warna indikator
 * 
 * Usage:
 *   const monitor = new DeviceStatusMonitor({
 *       interval: 1000,
 *       timeout: 10,
 *       apiUrl: '/api/monitoring/realtime/latest'
 *   });
 *   monitor.start();
 *   monitor.on('statusChange', (device) => console.log('Status changed:', device));
 */

class DeviceStatusMonitor {
    constructor(options = {}) {
        this.interval = options.interval || 1000; // Default 1 detik
        this.timeout = options.timeout || 10; // Default 10 detik untuk OFFLINE threshold
        this.apiUrl = options.apiUrl || '/api/monitoring/realtime/latest';
        this.pollInterval = null;
        this.lastStatus = {}; // Track perubahan status
        this.listeners = {};
        this.isRunning = false;
        this.localTimestamps = {}; // Untuk calculate seconds_ago secara akurat
    }

    /**
     * Start real-time monitoring (polling setiap 1 detik)
     */
    start() {
        if (this.isRunning) return;
        
        console.log(`🚀 Device Status Monitor started (interval: ${this.interval}ms, timeout: ${this.timeout}s)`);
        
        // Poll immediately
        this.fetchAndUpdate();
        
        // Then poll setiap interval
        this.pollInterval = setInterval(() => {
            this.fetchAndUpdate();
        }, this.interval);
        
        this.isRunning = true;
    }

    /**
     * Stop monitoring
     */
    stop() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.pollInterval = null;
        }
        this.isRunning = false;
        console.log('⏹️ Device Status Monitor stopped');
    }

    /**
     * Fetch data dari API dan update status
     */
    async fetchAndUpdate() {
        try {
            // Parameter: timeout untuk threshold OFFLINE
            const url = `${this.apiUrl}?timeout=${this.timeout}`;
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                console.error(`API Error: ${response.status}`);
                return;
            }

            const result = await response.json();
            
            if (!result.success || !result.data) {
                console.error('Invalid API response:', result);
                return;
            }

            // Normalkan response (bisa single object atau array)
            const devices = Array.isArray(result.data) ? result.data : [result.data];
            
            // Get server timestamp dari API response
            const serverTime = new Date(result.timestamp);
            
            // Update setiap device
            devices.forEach(device => {
                this.updateDeviceStatus(device, serverTime);
            });

        } catch (error) {
            console.error('Fetch error:', error);
        }
    }

    /**
     * Update status device individual
     */
    updateDeviceStatus(device, serverTime) {
        const deviceId = device.id;
        const oldStatus = this.lastStatus[deviceId];

        // Hitung seconds_ago lebih akurat menggunakan server time
        const lastUpdate = device.last_update ? new Date(device.last_update) : null;
        const secondsAgo = lastUpdate 
            ? Math.floor((serverTime - lastUpdate) / 1000)
            : null;

        // Build current status object
        const currentStatus = {
            id: device.id,
            device_id: device.device_id,
            device_name: device.device_name,
            location: device.location,
            esp_online: device.esp_online,
            esp_status: device.esp_status, // ONLINE, OFFLINE, DISCONNECTED
            esp_status_color: device.esp_status_color, // success, warning, danger
            seconds_ago: secondsAgo,
            last_update: device.last_update,
            temperature: device.temperature,
            temp_status: device.temp_status,
            humidity: device.humidity,
            humidity_status: device.humidity_status,
            monitoring_status: device.monitoring_status
        };

        // Debug log
        console.log(`Device #${deviceId} [${device.device_name}]: ${device.esp_status} (${secondsAgo}s ago)`);

        // Simpan timestamp local untuk next interval calculation
        this.localTimestamps[deviceId] = {
            lastUpdate: lastUpdate,
            serverTime: serverTime,
            secondsAgo: secondsAgo
        };

        // Trigger event jika status berubah
        if (oldStatus && oldStatus.esp_status !== currentStatus.esp_status) {
            console.warn(`⚠️ Status Change Detected! Device #${deviceId}: ${oldStatus.esp_status} → ${currentStatus.esp_status}`);
            this.emit('statusChange', currentStatus, oldStatus);
        }

        // Update display/indicators
        this.updateUI(currentStatus);

        // Store current status
        this.lastStatus[deviceId] = currentStatus;
    }

    /**
     * Hitung seconds_ago secara real-time (tanpa fetch)
     * Berguna untuk memperbarui counter di UI setiap frame
     */
    getSecondsAgo(deviceId) {
        const timestamps = this.localTimestamps[deviceId];
        if (!timestamps) return null;

        // Hitung: (now - lastUpdate) dalam seconds
        const now = new Date();
        const elapsed = Math.floor((now - timestamps.lastUpdate) / 1000);
        
        // Jangan biarkan lebih dari ~5 detik di atas server value
        // (untuk handle clock skew antara server dan client)
        return elapsed;
    }

    /**
     * Format waktu untuk display (e.g., "3 detik lalu")
     */
    formatTimeAgo(seconds) {
        if (seconds === null || seconds === undefined) return 'Tidak ada data';
        
        if (seconds < 60) {
            return `${seconds}d ${seconds === 1 ? 'detik' : 'detik'} lalu`;
        } else {
            const minutes = Math.floor(seconds / 60);
            return `${minutes}m ${minutes === 1 ? 'menit' : 'menit'} lalu`;
        }
    }

    /**
     * Update UI - ganti dengan HTML elements yang sesuai
     */
    updateUI(device) {
        // Update indikator device
        const indicator = document.getElementById(`device-indicator-${device.id}`);
        if (indicator) {
            // Update warna status
            indicator.className = `device-indicator status-${device.esp_status_color}`;
            
            // Update teks status
            const statusEl = indicator.querySelector('.device-status');
            if (statusEl) {
                statusEl.textContent = device.esp_status;
                statusEl.className = `device-status badge badge-${device.esp_status_color}`;
            }

            // Update seconds ago
            const timeEl = indicator.querySelector('.device-time-ago');
            if (timeEl && device.seconds_ago !== null) {
                timeEl.textContent = this.formatTimeAgo(device.seconds_ago);
                timeEl.setAttribute('data-seconds', device.seconds_ago);
            }

            // Update temperature/humidity
            const tempEl = indicator.querySelector('.device-temperature');
            if (tempEl && device.temperature !== null) {
                tempEl.textContent = `${device.temperature.toFixed(1)}°C`;
                tempEl.className = `device-temperature badge badge-${device.temp_status}`;
            }

            const humidEl = indicator.querySelector('.device-humidity');
            if (humidEl && device.humidity !== null) {
                humidEl.textContent = `${device.humidity.toFixed(0)}%`;
                humidEl.className = `device-humidity badge badge-${device.humidity_status}`;
            }
        }

        // Trigger custom event untuk aplikasi lain mendengarkan
        window.dispatchEvent(new CustomEvent('deviceStatusUpdated', { 
            detail: device 
        }));
    }

    /**
     * Event listener - subscribe ke status changes
     */
    on(eventName, callback) {
        if (!this.listeners[eventName]) {
            this.listeners[eventName] = [];
        }
        this.listeners[eventName].push(callback);
    }

    /**
     * Emit event ke semua listeners
     */
    emit(eventName, ...args) {
        if (!this.listeners[eventName]) return;
        
        this.listeners[eventName].forEach(callback => {
            try {
                callback(...args);
            } catch (error) {
                console.error(`Error in listener for '${eventName}':`, error);
            }
        });
    }

    /**
     * Get current status dari device tertentu
     */
    getStatus(deviceId) {
        return this.lastStatus[deviceId] || null;
    }

    /**
     * Get all device statuses
     */
    getAllStatuses() {
        return Object.values(this.lastStatus);
    }
}

/**
 * CONTOH PENGGUNAAN
 */
document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi monitor
    const monitor = new DeviceStatusMonitor({
        interval: 1000,  // Poll setiap 1 detik
        timeout: 10,     // Device offline jika > 10 detik tanpa data
        apiUrl: '/api/monitoring/realtime/latest'
    });

    // Listen ke status changes
    monitor.on('statusChange', (current, previous) => {
        console.log(`Device #${current.id} status changed: ${previous.esp_status} → ${current.esp_status}`);
        
        // Trigger notifikasi ke user
        if (current.esp_status === 'OFFLINE') {
            notifyDeviceOffline(current);
        } else if (current.esp_status === 'ONLINE') {
            notifyDeviceOnline(current);
        }
    });

    // Start monitoring
    monitor.start();

    // Update seconds_ago display setiap 500ms (smooth countdown)
    setInterval(() => {
        monitor.getAllStatuses().forEach(device => {
            const secondsAgo = monitor.getSecondsAgo(device.id);
            if (secondsAgo !== null) {
                const timeEl = document.querySelector(`#device-indicator-${device.id} .device-time-ago`);
                if (timeEl) {
                    timeEl.textContent = monitor.formatTimeAgo(secondsAgo);
                }
            }
        });
    }, 500);

    // Jika halaman akan di-close, stop monitoring
    window.addEventListener('beforeunload', () => {
        monitor.stop();
    });

    // Export untuk akses global
    window.deviceMonitor = monitor;
});

/**
 * Helper functions untuk notifikasi
 */
function notifyDeviceOffline(device) {
    console.error(`🔴 Device ${device.device_name} has gone OFFLINE!`);
    
    // Bisa tambahkan sound alert
    if (window.notificationSound) {
        window.notificationSound.play().catch(() => {});
    }

    // Bisa tambahkan browser notification
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification('Device Offline', {
            body: `${device.device_name} di ${device.location} has gone offline!`,
            tag: `device-${device.id}`,
            requireInteraction: true
        });
    }
}

function notifyDeviceOnline(device) {
    console.log(`🟢 Device ${device.device_name} is now ONLINE`);
}
