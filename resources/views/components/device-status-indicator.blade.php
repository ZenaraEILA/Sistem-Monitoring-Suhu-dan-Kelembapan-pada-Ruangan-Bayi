<div id="device-indicator-{{ $device->id }}" class="device-indicator status-success">
    <!-- Device Header -->
    <div class="device-header">
        <h5 class="device-name">{{ $device->device_name }}</h5>
        <p class="device-location text-muted">📍 {{ $device->location }}</p>
    </div>

    <!-- Status Badge -->
    <div class="device-status-section">
        <span id="device-status-{{ $device->id }}" class="device-status badge badge-success">
            <i class="fas fa-circle"></i> ONLINE
        </span>
        <small id="device-time-ago-{{ $device->id }}" class="device-time-ago text-muted">
            <i class="fas fa-clock"></i> Checking...
        </small>
    </div>

    <!-- Temperature & Humidity Display -->
    <div class="device-data-section">
        <div class="data-item temperature">
            <span class="data-label">🌡️ Suhu</span>
            <span id="device-temperature-{{ $device->id }}" class="device-temperature badge badge-info">
                --°C
            </span>
        </div>
        
        <div class="data-item humidity">
            <span class="data-label">💨 Kelembapan</span>
            <span id="device-humidity-{{ $device->id }}" class="device-humidity badge badge-secondary">
                --%
            </span>
        </div>
    </div>

    <!-- Status Details -->
    <div class="device-details-section">
        <small class="text-muted">
            <strong>Last Update:</strong>
            <span id="device-last-update-{{ $device->id }}" class="font-monospace">-</span>
        </small>
        <small class="text-muted d-block">
            <strong>Device ID:</strong>
            <code>{{ $device->device_id }}</code>
        </small>
    </div>
</div>

<!-- STYLING -->
<style>
.device-indicator {
    border: 2px solid #ccc;
    border-radius: 8px;
    padding: 15px;
    margin: 10px 0;
    transition: all 0.3s ease;
    background: #f9f9f9;
}

.device-indicator.status-success {
    border-color: #28a745;
    background: #f0fff4;
}

.device-indicator.status-warning {
    border-color: #ffc107;
    background: #fffff3;
}

.device-indicator.status-danger {
    border-color: #dc3545;
    background: #fff5f5;
}

.device-header {
    margin-bottom: 10px;
}

.device-header h5 {
    margin: 0;
    font-weight: 600;
}

.device-name {
    color: #333;
}

.device-location {
    font-size: 0.85rem !important;
    margin: 5px 0 0 0;
}

.device-status {
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.device-status i {
    animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.device-time-ago {
    display: block;
    margin-top: 5px;
    font-size: 0.85rem;
}

.device-status-section {
    margin-bottom: 12px;
}

.device-data-section {
    display: flex;
    gap: 15px;
    margin: 12px 0;
    padding: 10px 0;
    border-top: 1px solid rgba(0,0,0,0.1);
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.data-item {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.data-label {
    font-weight: 500;
    font-size: 0.9rem;
}

.device-temperature,
.device-humidity {
    font-size: 1.1rem;
    font-weight: 600;
    padding: 5px 10px;
}

.device-details-section {
    font-size: 0.85rem;
    padding-top: 8px;
    border-top: 1px solid rgba(0,0,0,0.05);
}

.device-details-section small {
    display: block;
    margin: 3px 0;
}
</style>
