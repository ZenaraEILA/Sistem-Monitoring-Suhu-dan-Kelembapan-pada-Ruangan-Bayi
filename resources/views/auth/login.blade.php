<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Monitoring Suhu Bayi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0d6efd 0%, #e0f2ff 100%);
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
            padding: 35px 35px;
            animation: slideInUp 0.6s ease-out;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
            animation: slideInDown 0.6s ease-out;
        }

        .logo-icon {
            font-size: 42px;
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 10px;
            display: inline-block;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .login-header h1 {
            font-size: 26px;
            font-weight: 800;
            color: #2d3436;
            margin-bottom: 5px;
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
            padding: 14px 16px;
        }
        
        .alert i {
            margin-right: 8px;
            font-size: 16px;
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
            margin-bottom: 16px;
            position: relative;
        }

        .form-group label {
            display: flex;
            align-items: center;
            color: #2d3436;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }
        
        .form-group label i {
            margin-right: 8px;
            color: #0d6efd;
            font-size: 16px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 16px;
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
            border-color: #0d6efd;
            background: white;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12),
                        0 0 0 8px rgba(13, 110, 253, 0.06),
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
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
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
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
        }

        .btn-login:active {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(13, 110, 253, 0.4);
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
            color: #0d6efd;
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
            background: linear-gradient(90deg, #0d6efd, #0a58ca);
            transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .login-footer a:hover {
            color: #0a58ca;
        }

        .login-footer a:hover::after {
            width: 100%;
        }

        /* Custom segmented toggle */
        .method-toggle {
            display: flex;
            background: transparent;
            border-radius: 12px;
            padding: 4px;
            border: 2px solid #e8ebed;
            position: relative;
        }

        .method-toggle .btn-check:checked + .btn {
            background: #e0f2ff;
            color: #0d6efd;
            box-shadow: none;
            border-color: transparent;
            font-weight: 700;
        }

        .method-toggle .btn {
            flex: 1;
            border: none;
            border-radius: 8px;
            padding: 12px 10px;
            font-size: 14px;
            font-weight: 600;
            color: #636e72;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .method-toggle .btn:hover {
            color: #2d3436;
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

                <div class="form-group mb-3">
                    <label><i class="fas fa-id-card"></i> Login via</label>
                    <select id="identityType" onchange="switchIdentity()" class="form-select" style="padding: 12px 16px; border: 2px solid #e8ebed; border-radius: 12px; background: #f8f9fa; font-size: 15px;">
                        <option value="username">Username</option>
                        <option value="hospital_id">NISN / ID</option>
                        <option value="email">Email</option>
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label id="identityLabel"><i class="fas fa-user"></i> Username</label>
                    
                    <!-- Username Input -->
                    <input 
                        type="text" 
                        id="username_input" 
                        name="username" 
                        class="form-control" 
                        value="{{ old('username') }}" 
                        placeholder="Masukkan Username"
                    >
                    
                    <!-- NISN Input (Hidden) -->
                    <input 
                        type="text" 
                        id="hospital_id_input" 
                        name="hospital_id" 
                        class="form-control d-none" 
                        value="{{ old('hospital_id') }}" 
                        placeholder="Masukkan NISN / ID"
                        disabled
                    >

                    <!-- Email Input (Hidden) -->
                    <input 
                        type="email" 
                        id="email_input" 
                        name="email" 
                        class="form-control d-none" 
                        value="{{ old('email') }}" 
                        placeholder="Masukkan Email"
                        disabled
                    >
                </div>

                <div class="form-group">
                    <label>
                        <i class="fas fa-shield-alt"></i> Metode Login
                    </label>
                    <div class="method-toggle mt-2 mb-3">
                        <input type="radio" class="btn-check" name="login_method" id="methodPassword" value="password" autocomplete="off" checked onchange="toggleMethod()">
                        <label class="btn" for="methodPassword">
                            <i class="fas fa-lock"></i> Password
                        </label>

                        <input type="radio" class="btn-check" name="login_method" id="methodCode" value="code" autocomplete="off" onchange="toggleMethod()">
                        <label class="btn" for="methodCode">
                            <i class="fas fa-key"></i> Code Keamanan
                        </label>
                    </div>
                </div>

                <div class="form-group" id="credentialWrapper">
                    <label id="credentialLabel" for="credential">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input 
                        type="password" 
                        id="credential" 
                        name="credential" 
                        class="form-control @error('credential') is-invalid @enderror" 
                        placeholder="Masukkan password Anda"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="login-footer">
                <i class="fas fa-info-circle"></i> Hubungi Administrator jika Anda membutuhkan akses.
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function switchIdentity() {
            const type = document.getElementById('identityType').value;
            const label = document.getElementById('identityLabel');
            
            const userIn = document.getElementById('username_input');
            const hospIn = document.getElementById('hospital_id_input');
            const mailIn = document.getElementById('email_input');

            // Hide and disable all
            userIn.classList.add('d-none'); userIn.disabled = true;
            hospIn.classList.add('d-none'); hospIn.disabled = true;
            mailIn.classList.add('d-none'); mailIn.disabled = true;

            if (type === 'username') {
                label.innerHTML = '<i class="fas fa-user"></i> Username';
                userIn.classList.remove('d-none');
                userIn.disabled = false;
            } else if (type === 'hospital_id') {
                label.innerHTML = '<i class="fas fa-id-badge"></i> NISN / ID';
                hospIn.classList.remove('d-none');
                hospIn.disabled = false;
            } else if (type === 'email') {
                label.innerHTML = '<i class="fas fa-envelope"></i> Email';
                mailIn.classList.remove('d-none');
                mailIn.disabled = false;
            }
        }

        function toggleMethod() {
            const methodPassword = document.getElementById('methodPassword').checked;
            const label = document.getElementById('credentialLabel');
            const input = document.getElementById('credential');
            
            if (methodPassword) {
                label.innerHTML = '<i class="fas fa-lock"></i> Password';
                input.placeholder = 'Masukkan password Anda';
                input.type = 'password';
            } else {
                label.innerHTML = '<i class="fas fa-key"></i> Code Keamanan';
                input.placeholder = 'Masukkan code keamanan darurat';
                input.type = 'text';
            }
        }
    </script>
</body>
</html>
