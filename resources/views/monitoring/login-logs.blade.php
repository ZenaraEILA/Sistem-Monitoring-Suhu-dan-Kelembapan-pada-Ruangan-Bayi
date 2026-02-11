@extends('layouts.main')

@section('title', 'Riwayat Login - Sistem Monitoring')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3"><i class="fas fa-sign-in-alt"></i> Riwayat Login Petugas</h1>
        <p class="text-muted">Audit trail untuk melacak petugas yang masuk saat kejadian</p>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('login-logs.index') }}" method="get" class="row g-3">
            <div class="col-md-6">
                <label for="start_date" class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-6">
                <label for="end_date" class="form-label">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
                <a href="{{ route('login-logs.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo"></i> Reset Filter
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Total Login</h6>
                <h2 class="text-primary mb-0">{{ $loginLogs->total() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Admin</h6>
                <h2 class="text-success mb-0">{{ $loginLogs->items()->where('user.role', 'admin')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Petugas</h6>
                <h2 class="text-info mb-0">{{ $loginLogs->items()->where('user.role', 'petugas')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-1">Pengguna Unik</h6>
                <h2 class="text-warning mb-0">{{ $loginLogs->items()->unique('user_id')->count() }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Login History Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Nama Petugas</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Waktu Login</th>
                    <th>IP Address</th>
                    <th>Durasi Sejak Login</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loginLogs as $key => $log)
                <tr>
                    <td>{{ ($loginLogs->currentPage() - 1) * $loginLogs->perPage() + $loop->iteration }}</td>
                    <td>
                        <strong>{{ $log->user->name }}</strong>
                    </td>
                    <td>
                        <span class="badge {{ $log->user->role === 'admin' ? 'bg-danger' : 'bg-info' }}">
                            {{ ucfirst($log->user->role) }}
                        </span>
                    </td>
                    <td><code>{{ $log->user->email }}</code></td>
                    <td>
                        {{ $log->login_time->format('d-m-Y H:i:s') }}<br>
                        <small class="text-muted">{{ $log->login_time->diffForHumans() }}</small>
                    </td>
                    <td>
                        <code>{{ $log->ip_address }}</code>
                    </td>
                    <td>
                        @php
                            $diff = $log->login_time->diffInSeconds(now());
                            $hours = floor($diff / 3600);
                            $minutes = floor(($diff % 3600) / 60);
                        @endphp
                        @if($hours > 0)
                            {{ $hours }} jam {{ $minutes }} menit
                        @else
                            {{ $minutes }} menit
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <p class="text-muted mb-0">
                            <i class="fas fa-inbox"></i> Tidak ada data login
                        </p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-center mt-4">
    {{ $loginLogs->render('pagination::bootstrap-4') }}
</div>

<!-- Notes -->
<div class="alert alert-info mt-4">
    <h6 class="alert-heading"><i class="fas fa-lightbulb"></i> Catatan:</h6>
    <ul class="mb-0">
        <li>Tabel ini menampilkan riwayat login semua petugas</li>
        <li>Informasi ini berguna untuk audit trail saat terjadi insiden</li>
        <li>IP Address dapat digunakan untuk verifikasi lokasi login</li>
        <li>Filter berdasarkan tanggal untuk melihat aktivitas pada periode tertentu</li>
    </ul>
</div>

@endsection
