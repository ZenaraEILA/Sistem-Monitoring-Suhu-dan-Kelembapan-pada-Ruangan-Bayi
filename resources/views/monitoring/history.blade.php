@extends('layouts.main')

@section('title', 'Riwayat Monitoring - Sistem Monitoring Suhu Bayi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center">
            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                <i class="fas fa-history fs-4"></i>
            </div>
            <div>
                <h1 class="h3 mb-0 fw-bold text-dark">Riwayat Monitoring</h1>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Lihat dan filter data pemantauan ruangan dari waktu ke waktu</p>
            </div>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4 border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-filter text-primary me-2"></i> Filter Data</h5>
    </div>
    <div class="card-body p-4">
        <form method="GET" action="{{ route('monitoring.history') }}" class="row g-3">
            <div class="col-md-3">
                <label for="device_id" class="form-label">Device</label>
                <select name="device_id" id="device_id" class="form-select">
                    <option value="">-- Semua Device --</option>
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" {{ $selectedDevice == $device->id ? 'selected' : '' }}>
                            {{ $device->device_name }} ({{ $device->location }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-2">
                <label for="start_time" class="form-label">Jam Mulai</label>
                <input type="time" name="start_time" id="start_time" class="form-control" value="{{ $startTime }}">
            </div>
            <div class="col-md-2">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-2">
                <label for="end_time" class="form-label">Jam Akhir</label>
                <input type="time" name="end_time" id="end_time" class="form-control" value="{{ $endTime }}">
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100" style="border-radius: 10px; padding: 10px 14px;">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent border-0 pt-4 pb-2 px-4">
        <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-table text-primary me-2"></i> Data Monitoring</h5>
    </div>
    <div class="card-body px-0 pt-0">
    <div class="table-responsive px-4">
        <table class="table table-hover mb-0 table-sm">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>Lokasi</th>
                    <th>Suhu (°C)</th>
                    <th>Kelembapan (%)</th>
                    <th>Status</th>
                    <th>Rekomendasi</th>
                    <th>Catatan Tindakan</th>
                    <th>Waktu Pencatatan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monitorings as $monitoring)
                <tr class="{{ $monitoring->status === 'Tidak Aman' ? 'table-danger' : '' }}">
                    <td><strong>{{ $monitoring->device->device_name }}</strong></td>
                    <td>{{ $monitoring->device->location }}</td>
                    <td>
                        <span class="badge px-2 py-1 rounded-2" style="background-color: {{ $monitoring->temperature < 15 || $monitoring->temperature > 30 ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)' }}; color: {{ $monitoring->temperature < 15 || $monitoring->temperature > 30 ? '#ef4444' : '#10b981' }}; border: 1px solid {{ $monitoring->temperature < 15 || $monitoring->temperature > 30 ? 'rgba(239, 68, 68, 0.2)' : 'rgba(16, 185, 129, 0.2)' }}">
                            {{ number_format($monitoring->temperature, 2) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge px-2 py-1 rounded-2" style="background-color: {{ $monitoring->humidity < 35 || $monitoring->humidity > 60 ? 'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)' }}; color: {{ $monitoring->humidity < 35 || $monitoring->humidity > 60 ? '#ef4444' : '#10b981' }}; border: 1px solid {{ $monitoring->humidity < 35 || $monitoring->humidity > 60 ? 'rgba(239, 68, 68, 0.2)' : 'rgba(16, 185, 129, 0.2)' }}">
                            {{ number_format($monitoring->humidity, 2) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $monitoring->status === 'Aman' ? 'badge-aman' : 'badge-tidak-aman' }}">
                            {{ $monitoring->status }}
                        </span>
                    </td>
                    <td>
                        @php
                            $recs = $monitoring->recommendation_list;
                        @endphp
                        @if(count($recs) > 0)
                            <small class="text-muted">
                                @foreach($recs as $rec)
                                    • {{ $rec }}<br>
                                @endforeach
                            </small>
                        @else
                            <small class="text-success">✓ Normal</small>
                        @endif
                    </td>
                    <td>
                        @if($monitoring->action_note)
                            <small class="text-muted">{{ Str::limit($monitoring->action_note, 30) }}</small>
                        @else
                            <small class="text-secondary">-</small>
                        @endif
                        @if($monitoring->status === 'Tidak Aman')
                            <button class="btn btn-xs btn-warning" data-bs-toggle="modal" data-bs-target="#actionModal{{ $monitoring->id }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            

                        @endif
                    </td>
                    <td>{{ $monitoring->recorded_at->format('d-m-Y H:i:s') }}<br><small class="text-muted">{{ $monitoring->recorded_at->diffForHumans() }}</small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#detailModal{{ $monitoring->id }}" title="Lihat Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <p class="text-muted mb-0">Tidak ada data monitoring</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
    </div>
    <div class="card-footer px-4 pb-3 pt-3 bg-white" style="position: sticky; bottom: 0; z-index: 100; border-top: 1px solid rgba(0,0,0,0.05) !important; border-radius: 0 0 16px 16px; box-shadow: 0 -8px 20px rgba(0,0,0,0.03);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <small class="text-muted fw-bold">
                Total: {{ $monitorings->total() }} data
            </small>
            <div class="pagination-container m-0">
                {{ $monitorings->links() }}
            </div>
        </div>
    </div>
</div>


<!-- Modals for Action Notes -->
@foreach($monitorings as $monitoring)
    @if($monitoring->status === 'Tidak Aman')
        <!-- Action Modal -->
        <div class="modal fade" id="actionModal{{ $monitoring->id }}" tabindex="-1" aria-labelledby="actionModalLabel{{ $monitoring->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header bg-warning bg-opacity-10 border-warning border-opacity-25">
                        <h5 class="modal-title text-warning-emphasis fw-bold" id="actionModalLabel{{ $monitoring->id }}">
                            <i class="fas fa-edit me-2"></i>Catatan Tindakan
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('monitoring.update-action', $monitoring->id) }}" method="post">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label for="action_note_{{ $monitoring->id }}" class="form-label fw-semibold">Deskripsi Tindakan</label>
                                <textarea name="action_note" id="action_note_{{ $monitoring->id }}" class="form-control" rows="4" placeholder="Tulis tindakan apa yang telah dilakukan..." required>{{ $monitoring->action_note }}</textarea>
                                <small class="text-muted mt-2 d-block"><i class="fas fa-info-circle me-1"></i>Catat apa yang telah dilakukan untuk mengatasi kondisi tidak aman ini.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-top-0 bg-light rounded-bottom">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning text-dark fw-semibold px-4"><i class="fas fa-save me-2"></i>Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Detail Modal -->
    <div class="modal fade" id="detailModal{{ $monitoring->id }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $monitoring->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-info bg-opacity-10 border-info border-opacity-25">
                    <h5 class="modal-title text-info-emphasis fw-bold" id="detailModalLabel{{ $monitoring->id }}">
                        <i class="fas fa-info-circle me-2"></i>Detail Monitoring
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <table class="table table-borderless table-sm mb-0">
                        <tr><td width="35%" class="text-muted fw-semibold">Device</td><td>: <strong>{{ $monitoring->device->device_name }}</strong></td></tr>
                        <tr><td class="text-muted fw-semibold">Lokasi</td><td>: {{ $monitoring->device->location }}</td></tr>
                        <tr><td class="text-muted fw-semibold">Suhu</td><td>: {{ $monitoring->temperature }} °C</td></tr>
                        <tr><td class="text-muted fw-semibold">Kelembapan</td><td>: {{ $monitoring->humidity }} %</td></tr>
                        <tr><td class="text-muted fw-semibold">Status</td><td>: 
                            <span class="badge {{ $monitoring->status === 'Aman' ? 'bg-success' : 'bg-danger' }}">{{ $monitoring->status }}</span>
                        </td></tr>
                        <tr><td class="text-muted fw-semibold">Waktu</td><td>: {{ $monitoring->recorded_at->format('d M Y, H:i:s') }}</td></tr>
                    </table>
                    
                    @php $recs = $monitoring->recommendation_list; @endphp
                    @if(count($recs) > 0)
                    <div class="mt-3 p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded">
                        <h6 class="fw-bold mb-2 text-danger"><i class="fas fa-exclamation-triangle me-1"></i> Rekomendasi:</h6>
                        <ul class="mb-0 text-danger small ps-3">
                            @foreach($recs as $rec)
                                <li>{{ $rec }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    @if($monitoring->action_note)
                    <div class="mt-3 p-3 bg-light rounded border">
                        <h6 class="fw-bold mb-1"><i class="fas fa-clipboard-check text-success me-1"></i> Catatan Tindakan:</h6>
                        <p class="mb-0 text-muted small">{{ $monitoring->action_note }}</p>
                    </div>
                    @endif
                </div>
                <div class="modal-footer border-top-0 bg-light rounded-bottom">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<style>
    .badge-aman {
        background-color: rgba(16, 185, 129, 0.1) !important;
        color: #10b981 !important;
        border: 1px solid rgba(16, 185, 129, 0.2);
        padding: 6px 12px;
        font-weight: 600;
        border-radius: 8px;
    }

    .badge-tidak-aman {
        background-color: rgba(239, 68, 68, 0.1) !important;
        color: #ef4444 !important;
        border: 1px solid rgba(239, 68, 68, 0.2);
        padding: 6px 12px;
        font-weight: 600;
        border-radius: 8px;
    }

    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 6px;
    }
    
    .table-danger {
        background-color: rgba(239, 68, 68, 0.04) !important;
    }
    
    .pagination-container .pagination {
        margin-bottom: 0 !important;
    }
</style>
<script>
    // Pindahkan semua modal ke body untuk menghindari bug z-index & backdrop
    document.querySelectorAll('.modal').forEach(function(modal) {
        document.body.appendChild(modal);
    });
</script>
@endsection
