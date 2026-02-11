<!-- AC Control Widget -->
@if($device->ac_enabled && (auth()->user()->role === 'admin' || auth()->user()->role === 'petugas'))
<div class="ac-control-widget border-top pt-3 mt-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0"><i class="fas fa-fan"></i> Kontrol AC</h6>
        <small class="badge {{ $device->ac_status ? 'bg-success' : 'bg-secondary' }}">
            {{ $device->ac_status ? 'AKTIF' : 'TIDAK AKTIF' }}
        </small>
    </div>

    <!-- AC Set Point Display -->
    <div class="ac-set-point-display mb-3 p-3 bg-light rounded">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small class="d-block text-muted">Suhu Set Point AC</small>
                <h4 class="mb-0">{{ number_format($device->ac_set_point, 1) }}°C</h4>
                <small class="text-muted">Range: {{ $device->ac_min_temp }}°C - {{ $device->ac_max_temp }}°C</small>
            </div>
            <div style="font-size: 2.5rem; color: #0d6efd; opacity: 0.3;">
                <i class="fas fa-snowflake"></i>
            </div>
        </div>
    </div>

    <!-- AC Control Buttons -->
    <div class="ac-control-buttons mb-3">
        <div class="btn-group w-100" role="group">
            <button type="button" class="btn btn-sm btn-outline-primary ac-btn-decrease" 
                    data-device-id="{{ $device->id }}" 
                    data-action="decrease"
                    {{ !$device->ac_status ? 'disabled' : '' }}>
                <i class="fas fa-minus"></i> Turunkan
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary ac-btn-toggle" 
                    data-device-id="{{ $device->id }}"
                    data-action="{{ $device->ac_status ? 'turn_off' : 'turn_on' }}">
                <i class="fas {{ $device->ac_status ? 'fa-power-off' : 'fa-plug' }}"></i> 
                {{ $device->ac_status ? 'Matikan' : 'Nyalakan' }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary ac-btn-increase" 
                    data-device-id="{{ $device->id }}" 
                    data-action="increase"
                    {{ !$device->ac_status ? 'disabled' : '' }}>
                <i class="fas fa-plus"></i> Naikkan
            </button>
        </div>
    </div>

    <!-- Temperature Recommendation -->
    @php
        $currentTemp = $monitoring->temperature ?? 25;
        $recommendation = null;
        
        if ($currentTemp > 30) {
            $recommendation = [
                'type' => 'danger',
                'icon' => 'fa-fire',
                'message' => '🌡️ Suhu tinggi! Klik untuk turunkan AC',
                'action' => 'decrease'
            ];
        } elseif ($currentTemp < 15) {
            $recommendation = [
                'type' => 'info',
                'icon' => 'fa-snowflake',
                'message' => '❄️ Suhu rendah! Klik untuk naikkan AC',
                'action' => 'increase'
            ];
        }
    @endphp

    @if($recommendation)
        <div class="alert alert-{{ $recommendation['type'] }} mb-0 py-2 px-3">
            <small><strong>{{ $recommendation['message'] }}</strong></small>
            <button type="button" class="btn btn-xs btn-light float-end ac-quick-action" 
                    data-device-id="{{ $device->id }}" 
                    data-action="{{ $recommendation['action'] }}">
                Atur sekarang
            </button>
        </div>
    @endif

    <!-- Recent Actions Log (minimal) -->
    @php
        $recentLogs = $device->acLogs()->orderByDesc('created_at')->limit(3)->get();
    @endphp
    @if(count($recentLogs) > 0)
        <div class="ac-logs-minimal mt-3">
            <small class="d-block text-muted mb-2"><strong>Tindakan Terakhir:</strong></small>
            @foreach($recentLogs as $log)
                <small class="d-block text-truncate">
                    <i class="fas {{ $log->action === 'increase' ? 'fa-arrow-up' : ($log->action === 'decrease' ? 'fa-arrow-down' : 'fa-power-off') }} text-{{ $log->status === 'success' ? 'success' : 'danger' }}"></i>
                    {{ ucfirst(str_replace('_', ' ', $log->action)) }} 
                    <span class="text-muted">{{ $log->created_at->diffForHumans() }}</span>
                    <span class="text-muted">by {{ $log->user->name ?? 'Unknown' }}</span>
                </small>
            @endforeach
        </div>
    @endif
</div>

<style>
    .ac-control-widget {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 8px;
        padding: 12px !important;
    }

    .ac-btn-decrease:hover,
    .ac-btn-increase:hover {
        background-color: #0d6efd !important;
        color: white !important;
    }

    .ac-set-point-display {
        background: linear-gradient(135deg, #e7f3ff 0%, #e0f2ff 100%) !important;
        border-left: 4px solid #0d6efd;
    }

    .ac-logs-minimal small {
        line-height: 1.6;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // AC Control Button Handlers
        document.querySelectorAll('.ac-btn-decrease, .ac-btn-increase, .ac-btn-toggle, .ac-quick-action').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const deviceId = this.dataset.deviceId;
                const action = this.dataset.action;
                
                acControl(deviceId, action, this);
            });
        });

        function acControl(deviceId, action, button) {
            // Disable button temporarily
            button.disabled = true;
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengirim...';

            // Send request to API
            fetch(`/api/ac-control/${action}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('auth_token') || ''}`
                },
                body: JSON.stringify({
                    device_id: deviceId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('success', data.message || 'Aksi berhasil');
                    // Reload page or update UI
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('error', data.message || 'Aksi gagal');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Error komunikasi dengan server');
            })
            .finally(() => {
                button.disabled = false;
                button.innerHTML = originalText;
            });
        }

        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            const container = document.querySelector('.container-fluid') || document.body;
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => alertDiv.remove(), 5000);
        }
    });
</script>
@endif
