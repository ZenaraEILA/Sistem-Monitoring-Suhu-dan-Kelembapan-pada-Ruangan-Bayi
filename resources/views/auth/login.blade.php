<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Monitoring Suhu Bayi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Favicon Medical Icon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><linearGradient id='grad1' x1='0%' y1='0%' x2='100%' y2='100%'><stop offset='0%' style='stop-color:%23ff6b6b;stop-opacity:1' /><stop offset='100%' style='stop-color:%234ecdc4;stop-opacity:1' /></linearGradient></defs><rect width='100' height='100' fill='white'/><g transform='translate(50,50)'><circle cx='0' cy='0' r='45' fill='url(%23grad1)' opacity='0.1' stroke='url(%23grad1)' stroke-width='2'/><path d='M -8,-25 L -8,5 C -8,10 -4,15 0,15 C 4,15 8,10 8,5 L 8,-25 C 8,-28 5,-30 0,-30 C -5,-30 -8,-28 -8,-25 Z' fill='%23ff6b6b'/><circle cx='0' cy='-22' r='3' fill='%23ff6b6b'/><path d='M -0.5,-8 L 0.5,-8 L 0.5,-2 C 0.5,0 -0.5,0 -0.5,-2 Z' fill='%23fff' opacity='0.6'/><path d='M 12,-10 Q 18,-15 20,-8 Q 18,0 12,5 Q 15,0 12,-10 Z' fill='%234ecdc4'/></g></svg>" />
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

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 50px 40px;
            animation: slideInUp 0.6s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
            animation: slideInDown 0.6s ease-out;
        }

        .logo-icon {
            font-size: 50px;
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

        .login-header h1 {
            font-size: 32px;
            font-weight: 800;
            color: #2d3436;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: #636e72;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .alert {
            border: 1px solid;
            border-radius: 12px;
            margin-bottom: 25px;
            animation: slideInDown 0.4s ease-out;
            font-size: 14px;
            padding: 16px 18px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.08) 0%, rgba(192, 57, 43, 0.06) 100%);
            color: #c0392b;
            border-color: #e74c3c;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.15);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(81, 207, 102, 0.08) 0%, rgba(64, 192, 87, 0.06) 100%);
            color: #27ae60;
            border-color: #51cf66;
            box-shadow: 0 4px 12px rgba(81, 207, 102, 0.15);
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
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #2d3436;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e8ebed;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8f9fa;
            color: #2d3436;
            font-family: inherit;
        }

        .form-group input::placeholder {
            color: #b2bec3;
            transition: color 0.3s ease;
        }

        .form-group input:focus::placeholder {
            color: #95a5a6;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.12),
                        0 0 0 8px rgba(102, 126, 234, 0.06),
                        inset 0 2px 4px rgba(0, 0, 0, 0.02);
            transform: translateY(-1px);
        }

        .form-group input:hover:not(:focus) {
            border-color: #d4d8db;
            background: #fbfbfc;
        }

        .form-group input.is-invalid {
            border-color: #e74c3c;
            background-color: rgba(231, 76, 60, 0.02);
        }

        .form-group input.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.12),
                        0 0 0 8px rgba(231, 76, 60, 0.06);
            border-color: #c0392b;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(102, 126, 234, 0.5), 
                        0 0 20px rgba(240, 147, 251, 0.3);
        }

        .btn-login:active {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-login:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #636e72;
            font-size: 14px;
            line-height: 1.6;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline;
            position: relative;
            padding-bottom: 2px;
        }

        .login-footer a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .login-footer a:hover {
            color: #764ba2;
        }

        .login-footer a:hover::after {
            width: 100%;
        }

        .demo-credentials {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-left: 4px solid #667eea;
            border-radius: 12px;
            padding: 16px 18px;
            margin-top: 25px;
            font-size: 12px;
            animation: slideInUp 0.6s ease-out 0.2s both;
            transition: all 0.3s ease;
        }

        .demo-credentials:hover {
            border-color: rgba(102, 126, 234, 0.35);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.12) 0%, rgba(118, 75, 162, 0.12) 100%);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        .demo-credentials-title {
            font-weight: 700;
            color: #667eea;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .demo-credentials-title i {
            font-size: 14px;
        }

        .demo-credentials-item {
            color: #636e72;
            margin-bottom: 8px;
            font-family: 'Courier New', monospace;
            line-height: 1.6;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.4);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .demo-credentials-item:last-child {
            margin-bottom: 0;
        }

        .demo-credentials-item:hover {
            background: rgba(255, 255, 255, 0.6);
        }

        .demo-credentials-item strong {
            color: #2d3436;
            font-weight: 600;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 35px 25px;
            }

            .login-header h1 {
                font-size: 26px;
            }

            .logo-icon {
                font-size: 40px;
            }

            .form-group input {
                padding: 12px 14px;
                font-size: 14px;
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
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h1>Monitoring Bayi</h1>
                <p>Sistem Monitoring Suhu & Kelembapan Ruang Perawatan</p>
            </div>

            @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong><i class="fas fa-exclamation-circle"></i> Gagal Login!</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if (Session::has('success'))
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i> {{ Session::get('success') }}
            </div>
            @endif

            @if (Session::has('error'))
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ Session::get('error') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

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
                        placeholder="Masukkan email Anda"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control @error('password') is-invalid @enderror" 
                        placeholder="Masukkan password Anda"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="login-footer">
                Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
            </div>

            <div class="demo-credentials">
                <div class="demo-credentials-title">
                    <i class="fas fa-info-circle"></i> Demo Credentials
                </div>
                <div class="demo-credentials-item">
                    <strong>Admin:</strong> admin@monitoring.local / admin123
                </div>
                <div class="demo-credentials-item">
                    <strong>Petugas:</strong> petugas@monitoring.local / petugas123
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
