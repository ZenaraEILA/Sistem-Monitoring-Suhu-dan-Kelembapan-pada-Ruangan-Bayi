<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Sistem Monitoring Suhu & Kelembapan Ruang Bayi')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --dark: #343a40;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .sidebar {
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            min-height: 100vh;
        }

        .sidebar .nav-link {
            color: #666;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #f8f9fa;
            color: var(--primary);
            border-left-color: var(--primary);
        }

        .card {
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 8px;
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, #0056b3 100%);
            color: white;
            border: none;
            border-radius: 8px 8px 0 0;
        }

        .btn-primary {
            background: var(--primary);
            border: none;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,123,255,0.3);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .status-safe {
            background-color: #d4edda;
            color: #155724;
        }

        .status-unsafe {
            background-color: #f8d7da;
            color: #721c24;
        }

        .temp-display {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--primary);
        }

        .humidity-display {
            font-size: 2.5rem;
            font-weight: bold;
            color: #17a2b8;
        }

        .table {
            background: white;
        }

        .table thead {
            background-color: #f8f9fa;
        }

        .table th {
            border-top: none;
            color: #666;
            font-weight: 600;
        }

        .alert {
            border: none;
            border-radius: 8px;
        }

        .form-control, .form-select {
            border: 1px solid #ddd;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
        }

        .device-card {
            background: white;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-radius: 8px;
            transition: all 0.3s;
        }

        .device-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .badge-aman {
            background-color: #28a745;
        }

        .badge-tidak-aman {
            background-color: #dc3545;
        }

        footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-top: 40px;
        }

        .content-wrapper {
            padding: 20px;
        }

        @media (max-width: 768px) {
            .temp-display,
            .humidity-display {
                font-size: 2rem;
            }

            .sidebar {
                display: none;
            }
        }
    </style>
    
    @yield('css')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-heartbeat text-primary"></i> Monitoring Bayi
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    @else
                    <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            @auth
            <nav class="col-md-2 d-md-block sidebar">
                <div class="position-sticky">
                    <ul class="nav flex-column mt-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-chart-line"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('monitoring*') ? 'active' : '' }}" href="{{ route('monitoring.history') }}">
                                <i class="fas fa-history"></i> Riwayat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('monitoring.chart') ? 'active' : '' }}" href="{{ route('monitoring.chart') }}">
                                <i class="fas fa-chart-area"></i> Grafik
                            </a>
                        </li>
                        @if(auth()->user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('device*') ? 'active' : '' }}" href="{{ route('device.index') }}">
                                <i class="fas fa-microchip"></i> Manajemen Device
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto content-wrapper">
                @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Error</h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if ($message = Session::get('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if ($message = Session::get('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @yield('content')
            </main>
            @else
            <!-- Full width for unauthenticated pages -->
            <main class="col-12">
                @yield('content')
            </main>
            @endauth
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container-fluid">
            <p class="mb-0">&copy; 2026 Sistem Monitoring Suhu & Kelembapan Ruang Perawatan Bayi. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('js')
</body>
</html>
