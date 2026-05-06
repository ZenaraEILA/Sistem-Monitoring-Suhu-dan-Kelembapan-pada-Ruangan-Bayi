@extends('layouts.main')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2">
                <i class="fas fa-user-plus"></i> Tambah User Baru
            </h1>
            <p class="text-muted">Buat akun untuk pekerja baru atau admin</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> Terdapat kesalahan pada input Anda:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Form Pembuatan Akun</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}">
                                <div class="form-text">Opsional. Dapat digunakan untuk login.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="hospital_id" class="form-label">NISN / ID Pegawai</label>
                                <input type="text" class="form-control" id="hospital_id" name="hospital_id" value="{{ old('hospital_id') }}">
                                <div class="form-text">Opsional. Nomor induk pegawai.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role Akses <span class="text-danger">*</span></label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="petugas" {{ old('role') == 'petugas' ? 'selected' : '' }}>Petugas (Standar)</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin (Penuh)</option>
                            </select>
                        </div>

                        <hr class="my-4">

                        <h6 class="mb-3"><i class="fas fa-lock"></i> Pengaturan Password</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="6">
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-4">
                            <i class="fas fa-info-circle"></i> <strong>Catatan:</strong> Code Keamanan (Password Darurat) akan di-generate secara otomatis dan ditampilkan setelah akun berhasil dibuat.
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Buat Akun User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Kebijakan Keamanan</h5>
                </div>
                <div class="card-body">
                    <p>Mulai sekarang, login ke dalam sistem dapat menggunakan kombinasi berikut:</p>
                    <ul>
                        <li><strong>ID Pegawai / Username / Email</strong></li>
                        <li><strong>Password / Code Keamanan</strong></li>
                    </ul>
                    <p>Fitur registrasi mandiri telah dimatikan. Semua pembuatan akun harus melalui Admin untuk menjaga keamanan data medis rumah sakit.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
