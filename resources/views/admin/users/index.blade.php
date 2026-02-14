{{-- Daftar semua user (Admin only)
File: resources/views/admin/users/index.blade.php
GET /admin/users
--}}

@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2">
                <i class="fas fa-users"></i> Manajemen User
            </h1>
            <p class="text-muted">Kelola akun user dan ubah role sesuai kebutuhan</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-white-50">Total User</p>
                            <h3 class="card-title">{{ $totalUsers }}</h3>
                        </div>
                        <i class="fas fa-users fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-white-50">Admin</p>
                            <h3 class="card-title">{{ $totalAdmins }}</h3>
                        </div>
                        <i class="fas fa-crown fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-white-50">Petugas</p>
                            <h3 class="card-title">{{ $totalPetugas }}</h3>
                        </div>
                        <i class="fas fa-user fa-3x opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-white-50">User Aktif</p>
                            <h3 class="card-title">{{ $activeUsers }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-3x opacity-25"></i>
                    </div>
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

    <!-- Users Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-list"></i> Daftar User
            </h5>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Login Terakhir</th>
                        <th style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    <tr>
                        <td>
                            <span class="badge bg-light text-dark">
                                {{ $users->firstItem() + $index }}
                            </span>
                        </td>
                        <td>
                            <strong>{{ $user->name }}</strong>
                        </td>
                        <td>
                            <code>{{ $user->email }}</code>
                        </td>
                        <td>
                            @if($user->role === 'admin')
                                <span class="badge bg-danger">
                                    <i class="fas fa-crown"></i> Admin
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-user"></i> Petugas
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle"></i> Aktif
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle"></i> Nonaktif
                                </span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $user->getLastLoginInfo() }}
                            </small>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="btn btn-sm btn-info" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-3"></i><br>
                            Tidak ada user
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer bg-light">
            {{ $users->links() }}
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }

    .badge {
        padding: 0.5rem 0.75rem;
        font-weight: 500;
    }
</style>
@endsection
