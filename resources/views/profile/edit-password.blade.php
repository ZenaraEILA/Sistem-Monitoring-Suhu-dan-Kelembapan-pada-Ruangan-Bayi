@extends('layouts.main')

@section('title', 'Ganti Password - Sistem Monitoring')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3"><i class="fas fa-key"></i> Ganti Password</h1>
        <p class="text-muted">Ubah password akun Anda untuk keamanan lebih baik</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0"><i class="fas fa-lock"></i> Ubah Password</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle"></i> <strong>Gagal!</strong> Ada kesalahan:
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <form action="{{ route('profile.update-password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">
                            <i class="fas fa-lock"></i> Password Saat Ini
                            <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control @error('current_password') is-invalid @enderror" 
                               id="current_password" 
                               name="current_password" 
                               required
                               autocomplete="current-password">
                        @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Masukkan password Anda yang sekarang</small>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-key"></i> Password Baru
                            <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required
                               autocomplete="new-password">
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Minimal 8 karakter dengan kombinasi huruf, angka, dan simbol</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">
                            <i class="fas fa-check"></i> Konfirmasi Password Baru
                            <span class="text-danger">*</span>
                        </label>
                        <input type="password" 
                               class="form-control @error('password_confirmation') is-invalid @enderror" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required
                               autocomplete="new-password">
                        @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Ulangi password baru Anda</small>
                    </div>

                    <hr>

                    <!-- Password Strength Indicator -->
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-chart-line"></i> Kekuatan Password
                        </label>
                        <div class="password-strength-indicator">
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" id="passwordStrength" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted d-block mt-2" id="passwordStrengthText">Masukkan password baru untuk melihat kekuatan</small>
                        </div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="alert alert-info mb-4">
                        <h6 class="mb-2"><i class="fas fa-lightbulb"></i> Persyaratan Password Kuat:</h6>
                        <ul class="mb-0 small">
                            <li>Minimal <strong>8 karakter</strong></li>
                            <li>Mengandung <strong>huruf besar</strong> (A-Z)</li>
                            <li>Mengandung <strong>huruf kecil</strong> (a-z)</li>
                            <li>Mengandung <strong>angka</strong> (0-9)</li>
                            <li>Mengandung <strong>simbol khusus</strong> (!@#$%^&*)</li>
                        </ul>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Ubah Password
                        </button>
                        <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Tips -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Tips Keamanan</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-warning"></i> Jangan pernah membagikan password Anda kepada siapa pun, bahkan kepada administrator!
                </div>
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle"></i> Ganti password secara berkala (minimal setiap 90 hari) untuk keamanan maksimal.
                </div>
                <div class="alert alert-success">
                    <i class="fas fa-check"></i> Gunakan password unik yang tidak digunakan di aplikasi lain.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-history"></i> Aktivitas</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Terakhir kali mengubah password: <strong>{{ Auth::user()->updated_at->format('d M Y H:i') }}</strong></p>
                <a href="{{ route('profile.show') }}" class="btn btn-sm btn-outline-secondary w-100">
                    Kembali ke Profil
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Password Strength Script -->
<script>
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    let strength = 0;
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('passwordStrengthText');

    // Check length
    if (password.length >= 8) strength += 20;
    if (password.length >= 12) strength += 10;

    // Check for lowercase
    if (/[a-z]/.test(password)) strength += 20;

    // Check for uppercase
    if (/[A-Z]/.test(password)) strength += 20;

    // Check for numbers
    if (/[0-9]/.test(password)) strength += 15;

    // Check for special characters
    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength += 15;

    // Update progress bar
    strengthBar.style.width = strength + '%';

    // Update color and text
    if (strength < 40) {
        strengthBar.className = 'progress-bar bg-danger';
        strengthText.textContent = 'Password Lemah - Tambahkan lebih banyak karakter dan variasi';
    } else if (strength < 70) {
        strengthBar.className = 'progress-bar bg-warning';
        strengthText.textContent = 'Password Sedang - Pertimbangkan untuk menambah simbol khusus';
    } else {
        strengthBar.className = 'progress-bar bg-success';
        strengthText.textContent = 'Password Kuat - Siap untuk digunakan!';
    }
});
</script>

@endsection
