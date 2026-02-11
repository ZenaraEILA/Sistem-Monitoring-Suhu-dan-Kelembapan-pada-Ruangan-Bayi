@extends('layouts.main')

@section('title', 'Edit Device - Sistem Monitoring Suhu Bayi')

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Device</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('device.update', $device->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group mb-3">
                        <label for="device_name" class="form-label">Nama Device</label>
                        <input type="text" id="device_name" name="device_name" class="form-control @error('device_name') is-invalid @enderror" 
                               value="{{ old('device_name', $device->device_name) }}" required>
                        @error('device_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="location" class="form-label">Lokasi</label>
                        <input type="text" id="location" name="location" class="form-control @error('location') is-invalid @enderror" 
                               value="{{ old('location', $device->location) }}" required>
                        @error('location')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Device ID (Tidak dapat diubah)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" value="{{ $device->device_id }}" disabled>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('{{ $device->device_id }}')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Device ID digunakan untuk identifikasi saat ESP mengirim data</small>
                    </div>

                    @if($device->monitorings->count() > 0)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Info:</strong> Device ini sudah memiliki {{ $device->monitorings->count() }} data monitoring.
                    </div>
                    @endif

                    <div class="d-grid gap-2 d-flex">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                        <a href="{{ route('device.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    var temp = document.createElement("textarea");
    temp.value = text;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand("copy");
    document.body.removeChild(temp);
    alert("Device ID telah dicopy!");
}
</script>
@endsection
