@extends('layouts.main')

@section('title', 'Profil Saya - Sistem Monitoring')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3"><i class="fas fa-user-circle"></i> Profil Saya</h1>
        <p class="text-muted">Lihat dan kelola informasi profil Anda</p>
    </div>
</div>

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <!-- Profile Card -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Profil</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 text-center">
                        <div class="profile-avatar">
                            <div class="avatar-circle bg-primary text-white" style="width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto;">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-bold text-muted" width="30%">Nama</td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Email</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Role</td>
                                <td>
                                    <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-info' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Dibuat</td>
                                <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-muted">Update Terakhir</td>
                                <td>{{ $user->updated_at->format('d M Y H:i') }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="d-flex gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Profil
                    </a>
                    <a href="{{ route('profile.edit-password') }}" class="btn btn-warning">
                        <i class="fas fa-key"></i> Ganti Password
                    </a>
                </div>
            </div>
        </div>

        <!-- Activity Log Preview -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-history"></i> Login Terakhir</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Terakhir login: {{ Auth::user()->created_at->diffForHumans() }}</p>
                <a href="{{ route('login-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-right"></i> Lihat Semua Login History
                </a>
            </div>
        </div>
    </div>

    <!-- Security Info Card -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Keamanan</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Status Akun</h6>
                    <div class="alert alert-success mb-0">
                        <i class="fas fa-check-circle"></i> Akun Aktif
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Verifikasi Email</h6>
                    <div class="alert alert-{{ $user->email_verified_at ? 'success' : 'warning' }} mb-0">
                        <i class="fas {{ $user->email_verified_at ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                        {{ $user->email_verified_at ? 'Terverifikasi' : 'Belum Terverifikasi' }}
                    </div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-2">Password</h6>
                    <p class="text-muted small">Password Anda aman dengan enkripsi bcrypt</p>
                    <a href="{{ route('profile.edit-password') }}" class="btn btn-sm btn-warning w-100">
                        <i class="fas fa-key"></i> Ubah Password
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Statistik</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Akses Sistem</span>
                    <span class="badge bg-success">Aktif</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Izin</span>
                    <span class="badge bg-info">{{ ucfirst($user->role) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
