@extends('layouts.main')

@section('title', 'Profil Saya - Sistem Monitoring')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3"><i class="fas fa-user-circle"></i> Profil Saya</h1>
        <p class="text-muted">Lihat dan kelola informasi profil Anda</p>
    </div>
</div>

@if (session('warning'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle"></i> {{ session('warning') }}
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
                        <div class="profile-avatar mb-3" id="photoContainer">
                            @if ($user->profile_photo_path)
                                <img src="{{ Storage::url($user->profile_photo_path) }}" 
                                     alt="Foto Profil" 
                                     class="rounded-circle" 
                                     style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #007bff;">
                            @else
                                <div class="avatar-circle bg-primary text-white" style="width: 120px; height: 120px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto;">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </div>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                            <i class="fas fa-camera"></i> {{ $user->profile_photo_path ? 'Ganti' : 'Upload' }} Foto
                        </button>
                        @if ($user->profile_photo_path)
                        <form action="{{ route('profile.delete-photo') }}" method="POST" class="d-inline" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus foto profil?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                        @endif
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

<!-- Upload Photo Modal -->
<div class="modal fade" id="uploadPhotoModal" tabindex="-1" aria-labelledby="uploadPhotoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="uploadPhotoModalLabel">
                    <i class="fas fa-camera"></i> {{ $user->profile_photo_path ? 'Ganti' : 'Upload' }} Foto Profil
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('profile.upload-photo') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="profile_photo" class="form-label">
                            <i class="fas fa-image"></i> Pilih Foto
                            <span class="text-danger">*</span>
                        </label>
                        <input type="file" 
                               class="form-control @error('profile_photo') is-invalid @enderror" 
                               id="profile_photo" 
                               name="profile_photo" 
                               accept="image/jpeg,image/png,image/gif,image/webp,image/bmp,image/svg+xml,image/tiff,.jpg,.jpeg,.png,.gif,.webp,.bmp,.svg,.tiff"
                               required
                               onchange="previewImage(event)">
                        @error('profile_photo')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted d-block mt-2">
                            <strong>Format yang di-support:</strong> JPG, JPEG, PNG, GIF, WebP, BMP, SVG, TIFF<br>
                            <strong>Ukuran maksimal:</strong> 5 MB<br>
                            <strong>Rekomendasi:</strong> Gunakan foto 1:1 (square) untuk hasil terbaik
                        </small>
                    </div>

                    <div class="mb-3 text-center" id="previewContainer" style="display: none;">
                        <label class="form-label d-block mb-2">Pratinjau</label>
                        <img id="previewImage" src="" alt="Preview" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb"></i> Tips:
                        <ul class="mb-0 mt-2 small">
                            <li>Gunakan foto yang jelas dan terlihat dengan baik</li>
                            <li>Ukuran foto 1:1 (square) akan terlihat lebih baik</li>
                            <li>Format yang di-support: JPG, PNG, GIF, WebP, BMP, SVG, TIFF</li>
                            <li>Ukuran file maksimal 5 MB</li>
                            <li>WebP dan TIFF akan otomatis dikonversi untuk kompatibilitas maksimal</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> {{ $user->profile_photo_path ? 'Ganti' : 'Upload' }} Foto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript for Image Preview -->
<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImage = document.getElementById('previewImage');
            const previewContainer = document.getElementById('previewContainer');
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}
</script>

@endsection
