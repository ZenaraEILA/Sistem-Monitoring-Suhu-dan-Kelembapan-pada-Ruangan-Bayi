<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary bg-opacity-10">
                <h5 class="mb-0"><i class="fas fa-layer-group"></i> Status Semua Device</h5>
            </div>
            <div class="card-body">
                <div class="row" id="devicesIndicatorContainer">
                    <div class="col-12">
                        <p class="text-muted text-center">Loading status device...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadAllDevicesStatus() {
        fetch('/api/monitoring/realtime/latest')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    renderDevicesStatus(data.data);
                }
            })
            .catch(error => console.error('Error loading devices status:', error));
    }

    function renderDevicesStatus(devices) {
        const container = document.getElementById('devicesIndicatorContainer');
        let html = '';

        devices.forEach(device => {
            const temp = device.temperature || '-';
            const humidity = device.humidity || '-';
            // Display status in Indonesian
            let espStatus = 'TIDAK TERHUBUNG';
            let espColor = '#6c757d'; // Gray
            
            if (device.esp_online) {
                espStatus = 'TERHUBUNG';
                espColor = '#28a745'; // Green
            } else if (device.esp_status === 'OFFLINE') {
                espStatus = 'OFFLINE';
                espColor = '#ffc107'; // Yellow
            }
            
            // Determine temp color
            let tempColor = '#28a745'; // Green - safe
            if (device.temp_status === 'danger') {
                tempColor = '#dc3545'; // Red
            } else if (device.temp_status === 'warning') {
                tempColor = '#ffc107'; // Yellow
            }

            // Determine humidity color
            let humColor = '#0dcaf0'; // Blue - safe
            if (device.humidity_status === 'warning') {
                humColor = '#ff9800'; // Orange
            }

            html += `
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-2">
                                <i class="fas fa-microchip text-primary"></i> ${device.device_name}
                            </h6>
                            <p class="small text-muted mb-2">${device.location}</p>
                            
                            <!-- Temperature -->
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small"><i class="fas fa-thermometer-half"></i> Suhu:</span>
                                <span class="badge" style="background-color: ${tempColor};">
                                    ${typeof temp === 'number' ? temp.toFixed(1) : temp}°C
                                </span>
                            </div>

                            <!-- Humidity -->
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small"><i class="fas fa-tint"></i> Kelembapan:</span>
                                <span class="badge" style="background-color: ${humColor};">
                                    ${typeof humidity === 'number' ? Math.round(humidity) : humidity}%
                                </span>
                            </div>

                            <!-- ESP Status -->
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="small"><i class="fas fa-wifi"></i> ESP:</span>
                                <span class="badge" style="background-color: ${espColor};">
                                    ${espStatus}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    // Load on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadAllDevicesStatus();
        // Update every 2 seconds
        setInterval(loadAllDevicesStatus, 2000);
    });
</script>
