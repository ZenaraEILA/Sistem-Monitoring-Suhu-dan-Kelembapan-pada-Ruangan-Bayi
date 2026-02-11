@extends('layouts.main')

@section('title', 'Tambah Device - Sistem Monitoring Suhu Bayi')

@section('content')
<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus"></i> Tambah Device Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('device.store') }}">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="device_name" class="form-label">Nama Device</label>
                        <input type="text" id="device_name" name="device_name" class="form-control @error('device_name') is-invalid @enderror" 
                               value="{{ old('device_name') }}" placeholder="Contoh: Ruang Bayi A" required>
                        @error('device_name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="location" class="form-label">Lokasi</label>
                        <input type="text" id="location" name="location" class="form-control @error('location') is-invalid @enderror" 
                               value="{{ old('location') }}" placeholder="Contoh: Lantai 3 - Ruang Perawatan" required>
                        @error('location')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Info:</strong> Device ID akan otomatis dibuat setelah device tersimpan.
                    </div>

                    <div class="d-grid gap-2 d-flex">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Device
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
@endsection
