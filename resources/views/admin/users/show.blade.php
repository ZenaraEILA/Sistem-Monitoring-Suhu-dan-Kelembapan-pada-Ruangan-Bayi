{{-- Detail user dengan form ubah role
File: resources/views/admin/users/show.blade.php
GET /admin/users/{id}
--}}

@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="h2">Detail User</h1>
                    <p class="text-muted">{{ $user->email }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> Terjadi kesalahan:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- User Information -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Informasi User
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label class="form-label text-muted small">Nama</label>
                        </div>
                        <div class="col-sm-8">
                            <p class="mb-0"><strong>{{ $user->name }}</strong></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label class="form-label text-muted small">Email</label>
                        </div>
                        <div class="col-sm-8">
                            <p class="mb-0"><code>{{ $user->email }}</code></p>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label class="form-label text-muted small">Role</label>
                        </div>
                        <div class="col-sm-8">
                            @if($user->role === 'admin')
                                <span class="badge bg-danger">
                                    <i class="fas fa-crown"></i> Admin
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-user"></i> Petugas
                                </span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label class="form-label text-muted small">Status</label>
                        </div>
                        <div class="col-sm-8">
                            @if($user->is_active)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Aktif
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle"></i> Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <label class="form-label text-muted small">Email Verified</label>
                        </div>
                        <div class="col-sm-8">
                            @if($user->email_verified_at)
                                <span class="badge bg-success">
                                    {{ $user->email_verified_at->format('d M Y H:i') }}
                                </span>
                            @else
                                <span class="badge bg-warning">Belum Verifikasi</span>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-sm-4">
                            <label class="form-label text-muted small">Login Terakhir</label>
                        </div>
                        <div class="col-sm-8">
                            <p class="mb-0 text-muted small">
                                {{ $user->getLastLoginInfo() }}
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-sm-4">
                            <label class="form-label text-muted small">Dibuat</label>
                        </div>
                        <div class="col-sm-8">
                            <p class="mb-0 text-muted small">
                                {{ $user->created_at->format('d M Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Management -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-edit"></i> Manajemen Role
                    </h5>
                </div>
                <div class="card-body">
                    @if($canChangeRole)
                        <form action="{{ route('admin.users.updateRole', $user) }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">
                                    <strong>Role Saat Ini</strong>
                                </label>
                                <div class="alert alert-light border">
                                    @if($user->role === 'admin')
                                        <span class="badge bg-danger">
                                            <i class="fas fa-crown"></i> Admin
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-user"></i> Petugas
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="role" class="form-label">
                                    <strong>Ubah Role Menjadi</strong>
                                </label>
                                <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">-- Pilih Role Baru --</option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                                        <i class="fas fa-crown"></i> Admin
                                    </option>
                                    <option value="petugas" {{ $user->role === 'petugas' ? 'selected' : '' }}>
                                        <i class="fas fa-user"></i> Petugas
                                    </option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback d-block">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle"></i>
                                <strong>Catatan:</strong> Perubahan role akan dicatat dalam log sistem untuk audit trail.
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </form>
                    @else
                        <div class="alert alert-warning border-warning">
                            <i class="fas fa-shield-alt"></i>
                            <strong>Proteksi Keamanan</strong><br><br>
                            Anda tidak dapat mengubah role akun Anda sendiri untuk alasan keamanan.
                            <br><br>
                            Jika Anda perlu meng-upgrade user lain menjadi admin, gunakan form di atas.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- User Status Management -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-toggle-on"></i> Status User
                    </h5>
                </div>
                <div class="card-body">
                    @if($user->is_active)
                        <p class="mb-3">User saat ini <strong>AKTIF</strong></p>
                        <form action="{{ route('admin.users.deactivate', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100"
                                    onclick="return confirm('Apakah Anda yakin ingin menonaktifkan user ini?')">
                                <i class="fas fa-ban"></i> Nonaktifkan User
                            </button>
                        </form>
                    @else
                        <p class="mb-3">User saat ini <strong>NONAKTIF</strong></p>
                        <form action="{{ route('admin.users.activate', $user) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-check-circle"></i> Aktifkan User
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
