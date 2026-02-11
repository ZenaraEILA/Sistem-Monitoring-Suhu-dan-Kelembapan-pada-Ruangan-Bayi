@extends('layouts.main')

@section('title', 'Riwayat Monitoring - Sistem Monitoring Suhu Bayi')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0"><i class="fas fa-history"></i> Riwayat Monitoring</h1>
    </div>
</div>

<!-- Filter -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter"></i> Filter Data</h5>
    </div>
    <div class="card-body">
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
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-table"></i> Data Monitoring</h5>
    </div>
    <div class="table-responsive">
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
                        <span class="badge" style="background-color: {{ $monitoring->temperature < 15 || $monitoring->temperature > 30 ? '#dc3545' : '#28a745' }}">
                            {{ number_format($monitoring->temperature, 2) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge" style="background-color: {{ $monitoring->humidity < 35 || $monitoring->humidity > 60 ? '#dc3545' : '#28a745' }}">
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
                            
                            <!-- Action Modal -->
                            <div class="modal fade" id="actionModal{{ $monitoring->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Catatan Tindakan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('monitoring.update-action', $monitoring->id) }}" method="post">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="action_note" class="form-label">Deskripsi Tindakan</label>
                                                    <textarea name="action_note" id="action_note" class="form-control" rows="4" required>{{ $monitoring->action_note }}</textarea>
                                                    <small class="text-muted">Catatan apa yang telah dilakukan untuk mengatasi kondisi ini</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </td>
                    <td>{{ $monitoring->recorded_at->format('d-m-Y H:i:s') }}<br><small class="text-muted">{{ $monitoring->recorded_at->diffForHumans() }}</small></td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Detail">
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
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                Total: {{ $monitorings->total() }} data
            </small>
            {{ $monitorings->links() }}
        </div>
    </div>
</div>

<style>
    .badge-aman {
        background-color: #28a745 !important;
        color: white;
    }

    .badge-tidak-aman {
        background-color: #dc3545 !important;
        color: white;
    }

    .btn-xs {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
@endsection
