<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Sistem Monitoring Suhu Bayi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            width: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 20px 0;
        }

        /* Animated background elements */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -50%;
            left: -50%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(30px); }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .register-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 500px;
        }

        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            animation: slideInUp 0.6s ease-out;
        }

        .register-header {
            text-align: center;
            margin-bottom: 35px;
            animation: slideInDown 0.6s ease-out;
        }

        .logo-icon {
            font-size: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 15px;
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .register-header h1 {
            font-size: 30px;
            font-weight: 800;
            color: #2d3436;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .register-header p {
            color: #636e72;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .alert {
            border: none;
            border-radius: 12px;
            margin-bottom: 25px;
            animation: slideInDown 0.4s ease-out;
            font-size: 14px;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.2);
        }

        .alert ul {
            margin-bottom: 0;
            list-style: none;
            padding-left: 20px;
        }

        .alert li {
            position: relative;
            padding-left: 10px;
            margin-bottom: 5px;
        }

        .form-group {
            margin-bottom: 18px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #2d3436;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e8ebed;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
            color: #2d3436;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-group input::placeholder {
            color: #b2bec3;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .form-row .form-group {
            margin-bottom: 0;
        }

        .btn-register {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 15px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-register:active {
            transform: translateY(-1px);
        }

        .register-footer {
            text-align: center;
            margin-top: 20px;
            color: #636e72;
            font-size: 14px;
        }

        .register-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .register-footer a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .password-requirements {
            background: rgba(102, 126, 234, 0.08);
            border-left: 3px solid #667eea;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #636e72;
            animation: slideInUp 0.8s ease-out;
        }

        .password-requirements-title {
            font-weight: 700;
            color: #667eea;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .password-requirements-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .password-requirements-list li {
            padding: 3px 0;
            padding-left: 20px;
            position: relative;
        }

        .password-requirements-list li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #51cf66;
            font-weight: bold;
        }

        @media (max-width: 576px) {
            .register-card {
                padding: 35px 25px;
            }

            .register-header h1 {
                font-size: 24px;
            }

            .logo-icon {
                font-size: 40px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-group input,
            .form-group select {
                padding: 11px 12px;
                font-size: 13px;
            }

            body::before,
            body::after {
                width: 300px;
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
        <div class="register-card">
            <div class="register-header">
                <div class="logo-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h1>Buat Akun Baru</h1>
                <p>Bergabunglah dengan sistem monitoring kami</p>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong><i class="fas fa-exclamation-circle"></i> Gagal Mendaftar!</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i> Nama Lengkap
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control @error('name') is-invalid @enderror" 
                        value="{{ old('name') }}" 
                        placeholder="Masukkan nama lengkap"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-control @error('email') is-invalid @enderror" 
                        value="{{ old('email') }}" 
                        placeholder="Masukkan alamat email"
                        required
                    >
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            placeholder="Minimal 8 karakter"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">
                            <i class="fas fa-shield-alt"></i> Konfirmasi
                        </label>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            class="form-control" 
                            placeholder="Ulangi password"
                            required
                        >
                    </div>
                </div>

                <div class="password-requirements">
                    <div class="password-requirements-title">
                        <i class="fas fa-info-circle"></i> Persyaratan Password
                    </div>
                    <ul class="password-requirements-list">
                        <li>Minimal 8 karakter</li>
                        <li>Gunakan huruf besar dan kecil</li>
                        <li>Gunakan angka</li>
                    </ul>
                </div>

                <div class="form-group">
                    <label for="role">
                        <i class="fas fa-user-tag"></i> Role Pengguna
                    </label>
                    <select 
                        id="role" 
                        name="role" 
                        class="form-control @error('role') is-invalid @enderror" 
                        required
                    >
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>
                            Admin - Akses Penuh
                        </option>
                        <option value="petugas" {{ old('role') == 'petugas' ? 'selected' : '' }}>
                            Petugas - Monitoring Saja
                        </option>
                    </select>
                </div>

                <button type="submit" class="btn btn-register">
                    <i class="fas fa-user-check"></i> Daftar Akun
                </button>
            </form>

            <div class="register-footer">
                Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
