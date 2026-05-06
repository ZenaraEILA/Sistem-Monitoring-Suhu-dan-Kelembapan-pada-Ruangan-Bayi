<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'Sistem Monitoring Suhu & Kelembapan Ruang Bayi')</title>
    
    <!-- Favicon Medical Icon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><defs><linearGradient id='grad1' x1='0%' y1='0%' x2='100%' y2='100%'><stop offset='0%' style='stop-color:%23ff6b6b;stop-opacity:1' /><stop offset='100%' style='stop-color:%234ecdc4;stop-opacity:1' /></linearGradient></defs><rect width='100' height='100' fill='white'/><g transform='translate(50,50)'><circle cx='0' cy='0' r='45' fill='url(%23grad1)' opacity='0.1' stroke='url(%23grad1)' stroke-width='2'/><path d='M -8,-25 L -8,5 C -8,10 -4,15 0,15 C 4,15 8,10 8,5 L 8,-25 C 8,-28 5,-30 0,-30 C -5,-30 -8,-28 -8,-25 Z' fill='%23ff6b6b'/><circle cx='0' cy='-22' r='3' fill='%23ff6b6b'/><path d='M -0.5,-8 L 0.5,-8 L 0.5,-2 C 0.5,0 -0.5,0 -0.5,-2 Z' fill='%23fff' opacity='0.6'/><path d='M 12,-10 Q 18,-15 20,-8 Q 18,0 12,5 Q 15,0 12,-10 Z' fill='%234ecdc4'/></g></svg>" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <style>
        :root {
            --primary: #0d6efd;
            --primary-dark: #0a58ca;
            --primary-light: #e0f2ff;
            --success: #198754;
            --danger: #dc3545;
            --warning: #ffc107;
            --info: #0dcaf0;
            --dark: #212529;
            --light: #f8f9fa;
            --border-radius: 12px;
        }

        * {
            transition: color 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }

        body {
            background-color: #f4f7f6;
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2d3748;
        }

        /* Navbar Enhancement - MEDICAL DASHBOARD THEME */
        .navbar {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.95) 0%, rgba(0, 86, 179, 0.95) 100%) !important;
            border-bottom: none;
            padding: 8px 0;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.3rem;
            color: white !important;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            margin-left: 20px;
        }

        .navbar-brand i {
            color: white;
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            margin: 0 5px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .navbar-nav .nav-link:hover {
            color: white !important;
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .navbar-toggler {
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'%3e%3c/path%3e%3c/svg%3e");
        }

        /* Status Monitoring Dropdown Styling */
        .status-monitoring-dropdown {
            width: 320px !important;
            padding: 20px;
            border: none !important;
            border-radius: 12px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15) !important;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
            top: 100% !important;
        }

        .status-monitoring-dropdown .dropdown-header {
            padding: 0 0 15px 0;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 15px;
            font-weight: 700;
            color: #0056b3;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .indicator-group {
            display: flex;
            justify-content: center;
            padding: 12px 0;
            max-width: 100%;
        }

        .indicator-item {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 90%;
            padding: 10px;
            border-radius: 8px;
            background: rgba(0, 86, 179, 0.02);
            transition: all 0.3s ease;
        }

        .indicator-item:hover {
            background: rgba(0, 86, 179, 0.06);
            transform: translateX(4px);
        }

        .indicator-light {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 26px;
            flex-shrink: 0;
            box-shadow: 0 0 12px rgba(0, 0, 0, 0.2), inset 0 1px 3px rgba(255, 255, 255, 0.3);
        }

        .indicator-light.temperature {
            background: linear-gradient(135deg, #28a745, #20c997);
            animation: pulse-green 2s infinite;
        }

        .indicator-light.temperature.warning {
            background: linear-gradient(135deg, #ff9800, #ffb74d);
            animation: pulse-orange 2s infinite;
        }

        .indicator-light.temperature.critical {
            background: linear-gradient(135deg, #dc3545, #ff6b6b);
            animation: pulse-red 2s infinite;
        }

        .indicator-light.humidity {
            background: linear-gradient(135deg, #0dcaf0, #00bcd4);
            animation: pulse-cyan 2s infinite;
        }

        .indicator-light.humidity.warning {
            background: linear-gradient(135deg, #ff9800, #ffb74d);
            animation: pulse-orange 2s infinite;
        }

        .indicator-light.esp-online {
            background: linear-gradient(135deg, #28a745, #20c997);
            animation: pulse-green 1.5s infinite;
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.4), inset 0 1px 3px rgba(255, 255, 255, 0.3);
        }

        .indicator-light.esp-offline {
            background: linear-gradient(135deg, #6c757d, #868e96);
            animation: none;
            opacity: 0.7;
        }

        @keyframes pulse-green {
            0%, 100% { box-shadow: 0 0 12px rgba(40, 167, 69, 0.4), inset 0 1px 3px rgba(255, 255, 255, 0.3); }
            50% { box-shadow: 0 0 24px rgba(40, 167, 69, 0.8), inset 0 1px 3px rgba(255, 255, 255, 0.3); }
        }

        @keyframes pulse-orange {
            0%, 100% { box-shadow: 0 0 12px rgba(255, 152, 0, 0.4), inset 0 1px 3px rgba(255, 255, 255, 0.3); }
            50% { box-shadow: 0 0 24px rgba(255, 152, 0, 0.8), inset 0 1px 3px rgba(255, 255, 255, 0.3); }
        }

        @keyframes pulse-red {
            0%, 100% { box-shadow: 0 0 12px rgba(220, 53, 69, 0.4), inset 0 1px 3px rgba(255, 255, 255, 0.3); }
            50% { box-shadow: 0 0 24px rgba(220, 53, 69, 0.8), inset 0 1px 3px rgba(255, 255, 255, 0.3); }
        }

        @keyframes pulse-cyan {
            0%, 100% { box-shadow: 0 0 12px rgba(13, 202, 240, 0.4), inset 0 1px 3px rgba(255, 255, 255, 0.3); }
            50% { box-shadow: 0 0 24px rgba(13, 202, 240, 0.8), inset 0 1px 3px rgba(255, 255, 255, 0.3); }
        }

        .indicator-info {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            gap: 4px;
        }

        .indicator-info small {
            color: #6c757d;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .indicator-value {
            color: #0056b3;
            font-size: 16px;
            font-weight: 700;
            font-family: 'Courier New', monospace;
        }

        .indicator-status {
            color: #6c757d;
            font-size: 12px;
            font-weight: 500;
        }

        .status-monitoring-dropdown .dropdown-divider {
            border-top: 2px solid #e9ecef;
            margin: 12px 0;
        }

        /* Device Selector Styling */
        .device-selector-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 0 15px 0;
            border-bottom: 2px solid #e9ecef;
            margin-bottom: 15px;
            gap: 10px;
        }

        .device-selector-label {
            font-weight: 700;
            color: #0056b3;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .device-selector-dropdown {
            flex-grow: 1;
            padding: 8px 12px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            background: white;
            color: #0056b3;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='%230056b3' d='M8 11L3 6h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 8px center;
            background-size: 12px;
            padding-right: 30px;
        }

        .device-selector-dropdown:hover {
            border-color: #0056b3;
            background-color: #f8f9fa;
            box-shadow: 0 2px 8px rgba(0, 86, 179, 0.1);
        }

        .device-selector-dropdown:focus {
            outline: none;
            border-color: #0056b3;
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
        }

        .device-selector-dropdown option {
            background: white;
            color: #0056b3;
            padding: 8px;
        }

        .clock-display {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            font-size: 13px;
            backdrop-filter: blur(10px);
        }

        .clock-display i {
            color: #ffc107;
            margin-right: 6px;
        }

        /* Sidebar Enhancement - FIXED POSITIONING */
        .sidebar {
            background: #ffffff;
            box-shadow: 4px 0 24px rgba(0,0,0,0.03);
            position: fixed;
            left: 0;
            top: 66px; /* Adjusted for slightly taller navbar */
            bottom: 0;
            width: 16.66%;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 24px 16px;
            z-index: 900;
            scroll-behavior: smooth;
            border-right: 1px solid rgba(0,0,0,0.03);
        }

        .sidebar .nav-link {
            color: #4a5568;
            border-radius: 12px;
            margin: 6px 0;
            padding: 12px 18px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-weight: 600;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 12px;
            color: inherit;
        }

        .sidebar .nav-link:hover {
            background: linear-gradient(90deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 110, 253, 0.04) 100%);
            color: var(--primary);
            border-left-color: var(--primary);
            transform: translateX(4px);
        }

        .sidebar .nav-link.active {
            background: linear-gradient(90deg, rgba(13, 110, 253, 0.12) 0%, rgba(13, 110, 253, 0.06) 100%);
            color: var(--primary);
            border-left-color: var(--primary);
            font-weight: 600;
            box-shadow: inset 0 2px 4px rgba(13, 110, 253, 0.1);
        }

        /* Card Enhancement */
        .card {
            border: 1px solid rgba(13, 110, 253, 0.1);
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border-radius: var(--border-radius);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .card:hover {
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
            border-color: rgba(13, 110, 253, 0.2);
            transform: translateY(-4px);
        }

        .card-header {
            background: transparent;
            color: var(--dark);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            border-radius: 16px 16px 0 0 !important;
            font-weight: 700;
            padding: 20px 24px;
        }
        .card-header i {
            color: var(--primary);
        }

        .card-header i {
            margin-right: 8px;
            font-size: 16px;
        }

        .card-body {
            padding: 24px;
        }

        /* Button Enhancement */
        .btn {
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(13, 110, 253, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #40c057 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(81, 207, 102, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(81, 207, 102, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #c0392b 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(231, 76, 60, 0.4);
        }

        .btn-outline-primary {
            border: 2px solid var(--primary);
            color: var(--primary);
            background: transparent;
        }

        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        /* Status Badge Enhancement */
        .status-badge {
            padding: 8px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .status-safe {
            background: linear-gradient(135deg, rgba(81, 207, 102, 0.1) 0%, rgba(64, 192, 87, 0.08) 100%);
            color: #27ae60;
            border: 1px solid rgba(81, 207, 102, 0.2);
        }

        .status-unsafe {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.1) 0%, rgba(192, 57, 43, 0.08) 100%);
            color: #c0392b;
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        .status-badge i {
            font-size: 14px;
        }

        /* Display Values */
        .temp-display {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }

        .humidity-display {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }

        /* Table Enhancement */
        .table {
            background: white;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: var(--border-radius);
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.08) 0%, rgba(13, 110, 253, 0.04) 100%);
        }

        .table thead th {
            border: none;
            color: var(--dark);
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 14px 16px;
            border-bottom: 2px solid rgba(13, 110, 253, 0.1);
        }

        .table tbody tr {
            border-bottom: 1px solid rgba(0,0,0,0.04);
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.04);
        }

        .table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
        }

        /* Alert Enhancement */
        .alert {
            border: 1px solid;
            border-radius: var(--border-radius);
            padding: 16px 18px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: slideInDown 0.4s ease-out;
        }

        .alert i {
            font-size: 18px;
            min-width: 24px;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .alert h6 {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-color: #fca5a5;
            color: #991b1b;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.15);
        }

        .alert-success {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-color: #86efac;
            color: #166534;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.15);
        }

        .alert-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-color: #fcd34d;
            color: #92400e;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.15);
        }

        .alert-info {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            border-color: #7dd3fc;
            color: #075985;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.15);
        }

        /* Global Alert Styling for Mobile Responsiveness */
        .global-alert {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 1080;
            width: 320px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
        }

        @media (max-width: 576px) {
            .global-alert {
                top: 70px;
                left: 5%;
                width: 90%;
                right: auto;
            }
        }

        /* Form Controls Enhancement */
        .form-control, .form-select {
            border: 2px solid #e8ebed;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8f9fa;
        }

        .form-control::placeholder {
            color: #b2bec3;
            transition: color 0.3s ease;
        }

        .form-control:focus::placeholder {
            color: #95a5a6;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12), 0 0 0 8px rgba(13, 110, 253, 0.06), inset 0 2px 4px rgba(0, 0, 0, 0.02);
            transform: translateY(-1px);
        }

        .form-control:hover:not(:focus), .form-select:hover:not(:focus) {
            border-color: #d4d8db;
            background: #fbfbfc;
        }

        .form-control.is-invalid {
            border-color: var(--danger);
            background-color: rgba(231, 76, 60, 0.02);
        }

        .form-control.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(231, 76, 60, 0.12), 0 0 0 8px rgba(231, 76, 60, 0.06);
        }

        .form-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 14px;
            letter-spacing: 0.3px;
        }

        .form-label i {
            margin-right: 6px;
            color: var(--primary);
        }

        /* Device Card Enhancement */
        .device-card {
            background: white;
            border: 1px solid rgba(13, 110, 253, 0.1);
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            border-radius: var(--border-radius);
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 20px;
        }

        .device-card:hover {
            box-shadow: 0 12px 32px rgba(0,0,0,0.12);
            border-color: rgba(13, 110, 253, 0.2);
            transform: translateY(-4px);
        }

        .device-card-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 12px;
            font-size: 1.1rem;
        }

        .device-card-icon {
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 12px;
        }

        /* Footer Enhancement */
        footer {
            background: linear-gradient(135deg, #2c3e50 0%, #1a252f 100%);
            color: white;
            padding: 24px 0;
            margin-top: 40px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        footer p {
            margin: 0;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* Content Wrapper - Adjust untuk Fixed Sidebar */
        .content-wrapper {
            padding: 24px;
            animation: fadeIn 0.5s ease-out;
            
            /* CRITICAL: Margin-left untuk accommodate fixed sidebar */
            margin-left: 16.66%;  /* Width col-md-2 (2/12 = 16.66%) */
            min-height: calc(100vh - 59px);  /* Full height minus navbar */
        }

        /* Clock Display */
        .clock-display {
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(5px);
            padding: 5px 18px;
            border-radius: 30px;
            letter-spacing: 1px;
            min-width: 140px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: inline-block;
        }

        .clock-display i {
            color: white;
            margin-right: 8px;
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* ========== HELP PAGE SIDEBAR - STICKY POSITIONING ========== */
        /* Untuk halaman bantuan agar sidebar tetap terlihat saat scroll */
        
        .help-sidebar {
            position: sticky;
            top: 80px;  /* Jarak dari navbar atas (navbar ~59px + padding ~20px) */
            max-height: calc(100vh - 100px);  /* Max height untuk sidebar scroll sendiri */
            overflow-y: auto;
        }
        
        /* Help Tips Card juga sticky */
        .help-tips {
            position: sticky;
            top: calc(80px + 500px);  /* Sticky di bawah daftar isi */
            max-height: calc(100vh - 150px);
            overflow-y: auto;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .temp-display,
            .humidity-display {
                font-size: 2rem;
            }

            .sidebar {
                display: none;
            }

            .clock-display {
                font-size: 0.85rem;
                min-width: 100px;
                padding: 6px 10px;
            }

            .card {
                margin-bottom: 20px;
            }

            .content-wrapper {
                padding: 16px;
                margin-left: 0 !important;  /* Reset margin untuk mobile */
            }
            
            /* Hide sidebar pada mobile */
            .sidebar {
                position: absolute;
                left: -100%;
                width: 100%;
                top: 66px;
            }

            /* Responsive Navbar */
            .navbar {
                padding: 8px 0;
            }

            .navbar-brand {
                font-size: 1.1rem;
            }

            .navbar-nav .nav-link {
                padding: 8px 10px;
                font-size: 14px;
            }

            /* Responsive Status Monitoring Dropdown */
            .status-monitoring-dropdown {
                width: 280px !important;
                padding: 15px;
                right: 0 !important;
                left: auto !important;
            }

            .indicator-light {
                width: 50px;
                height: 50px;
                font-size: 22px;
            }

            .indicator-info small {
                font-size: 10px;
            }

            .indicator-value {
                font-size: 14px;
            }

            .indicator-item {
                width: 100% !important;
                padding: 8px !important;
            }
        }

        @media (max-width: 480px) {
            .navbar-brand {
                font-size: 1rem;
            }

            .navbar-brand i {
                font-size: 1.2rem;
            }

            .clock-display {
                display: none;
            }

            .status-monitoring-dropdown {
                width: 260px !important;
                padding: 12px;
            }

            .indicator-light {
                width: 45px;
                height: 45px;
                font-size: 20px;
            }
        }
    
        /* --- GLOBAL ANIMATIONS --- */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card, .alert, .table-responsive {
            animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        .row > div:nth-child(1) > .card { animation-delay: 0.05s; }
        .row > div:nth-child(2) > .card { animation-delay: 0.1s; }
        .row > div:nth-child(3) > .card { animation-delay: 0.15s; }
        .row > div:nth-child(4) > .card { animation-delay: 0.2s; }

        .btn {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .device-card {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .device-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
        }

        /* Animasi pada Sidebar items */
        .sidebar-nav .sidebar-item {
            transition: all 0.3s ease;
        }
        .sidebar-nav .sidebar-item:hover {
            transform: translateX(5px);
        }
        
        /* Efek klik (Active) pada tombol */
        .btn:active {
            transform: translateY(1px) scale(0.98);
        }

</style>

    <!-- Real-Time Indicators CSS -->
    <style>
        /* Removed - indicators simplified */
    
        /* --- GLOBAL ANIMATIONS --- */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card, .alert, .table-responsive {
            animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        .row > div:nth-child(1) > .card { animation-delay: 0.05s; }
        .row > div:nth-child(2) > .card { animation-delay: 0.1s; }
        .row > div:nth-child(3) > .card { animation-delay: 0.15s; }
        .row > div:nth-child(4) > .card { animation-delay: 0.2s; }

        .btn {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }

        .device-card {
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .device-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
        }

        /* Animasi pada Sidebar items */
        .sidebar-nav .sidebar-item {
            transition: all 0.3s ease;
        }
        .sidebar-nav .sidebar-item:hover {
            transform: translateX(5px);
        }
        
        /* Efek klik (Active) pada tombol */
        .btn:active {
            transform: translateY(1px) scale(0.98);
        }

</style>
    
</head>
<body>
    <!-- ESP Connection Alerts (Global) -->
    <div id="espConnectedAlert" class="alert alert-success alert-dismissible fade show d-none global-alert" role="alert">
        <i class="fas fa-check-circle me-2"></i> <strong id="espConnectedMessage">✅ Koneksi ESP Berhasil!</strong>
        <p class="mb-0 mt-2" style="font-size: 0.85rem;">Sistem telah terhubung dan menerima data monitoring terbaru.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    
    <div id="espDisconnectedAlert" class="alert alert-danger alert-dismissible fade show d-none global-alert" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> <strong id="espDisconnectedMessage">⚠️ ESP Putus Koneksi!</strong>
        <p class="mb-0 mt-2" style="font-size: 0.85rem;">Perangkat tidak merespons. Periksa koneksi WiFi atau power supply.</p>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    
    <!-- Navbar - STICKY: Tetap terlihat saat scroll -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="position: sticky; top: 0; z-index: 1000;">
        <div class="container-fluid">
            <a class="navbar-brand text-white fw-bold" href="{{ route('dashboard') }}">
                <i class="fas fa-heartbeat text-white me-2"></i> Monitoring Bayi
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Mobile Main Navigation (Only visible on small screens) -->
                    @auth
                    <li class="nav-item d-md-none">
                        <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item d-md-none">
                        <a class="nav-link {{ request()->routeIs('monitoring*') ? 'active' : '' }}" href="{{ route('monitoring.history') }}">
                            <i class="fas fa-history"></i> Riwayat
                        </a>
                    </li>
                    <li class="nav-item d-md-none">
                        <a class="nav-link {{ request()->routeIs('monitoring.chart') ? 'active' : '' }}" href="{{ route('monitoring.chart') }}">
                            <i class="fas fa-chart-area"></i> Grafik
                        </a>
                    </li>
                    <li class="nav-item d-md-none">
                        <a class="nav-link {{ request()->routeIs('monitoring.hourly-trend') ? 'active' : '' }}" href="{{ route('monitoring.hourly-trend') }}">
                            <i class="fas fa-chart-simple"></i> Tren Harian
                        </a>
                    </li>
                    <li class="nav-item d-md-none">
                        <a class="nav-link {{ request()->routeIs('help*') ? 'active' : '' }}" href="{{ route('help.index') }}">
                            <i class="fas fa-question-circle"></i> Bantuan & Panduan
                        </a>
                    </li>
                    @if(auth()->user()->role === 'admin')
                    <li class="nav-item d-md-none border-bottom mb-2 pb-2">
                        <a class="nav-link {{ request()->routeIs('device*') ? 'active' : '' }}" href="{{ route('device.index') }}">
                            <i class="fas fa-microchip"></i> Manajemen Device
                        </a>
                    </li>
                    <li class="nav-item d-md-none border-bottom mb-2 pb-2">
                        <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users-cog"></i> Manajemen User
                        </a>
                    </li>
                    @else
                    <li class="nav-item d-md-none border-bottom mb-2 pb-2"></li>
                    @endif
                    @endauth

                    <!-- Clock - Simple & Reliable -->
                    <li class="nav-item me-3 d-flex align-items-center">
                        <div class="clock-display">
                            <i class="fas fa-clock"></i>
                            <span id="currentTime">00:00:00</span>
                        </div>
                    </li>

                    <!-- 🆕 Status Monitoring - Live Indicators -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="statusMonitoring" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false" title="Status Monitoring Real-Time">
                            <i class="fas fa-chart-line"></i> Status
                        </a>
                        
                        <div class="dropdown-menu dropdown-menu-end status-monitoring-dropdown" 
                             aria-labelledby="statusMonitoring">
                            
                            <!-- Device Selector - DYNAMIC -->
                            <div class="device-selector-group">
                                <label class="device-selector-label">Device:</label>
                                <select id="deviceSelector" class="device-selector-dropdown">
                                    <option value="">Loading devices...</option>
                                </select>
                            </div>

                            <div class="dropdown-header">Live Indicators</div>

                            <!-- Temperature Indicator -->
                            <div class="indicator-group">
                                <div class="indicator-item">
                                    <div class="indicator-light temperature" id="tempIndicator">
                                        <i class="fas fa-thermometer-half"></i>
                                    </div>
                                    <div class="indicator-info">
                                        <small>🌡 Suhu</small>
                                        <span class="indicator-value" id="tempValue">--°C</span>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-divider"></div>

                            <!-- Humidity Indicator -->
                            <div class="indicator-group">
                                <div class="indicator-item">
                                    <div class="indicator-light humidity" id="humidityIndicator">
                                        <i class="fas fa-droplet"></i>
                                    </div>
                                    <div class="indicator-info">
                                        <small>💧 Kelembapan</small>
                                        <span class="indicator-value" id="humidityValue">--%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-divider"></div>

                            <!-- ESP Status Indicator -->
                            <div class="indicator-group">
                                <div class="indicator-item">
                                    <div class="indicator-light esp-offline" id="espIndicator">
                                        <i class="fas fa-wifi-off" id="espIcon"></i>
                                    </div>
                                    <div class="indicator-info">
                                        <small>📡 ESP Status</small>
                                        <span class="indicator-value" id="espStatus">OFFLINE</span>
                                        <span class="indicator-status" id="espStatusText">Checking...</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </li>

                    @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-cog"></i> Profil</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-edit"></i> Edit Profil</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit-password') }}"><i class="fas fa-key"></i> Ganti Password</a></li>
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
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('monitoring.hourly-trend') ? 'active' : '' }}" href="{{ route('monitoring.hourly-trend') }}">
                                <i class="fas fa-chart-simple"></i> Tren Harian
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('help*') ? 'active' : '' }}" href="{{ route('help.index') }}">
                                <i class="fas fa-question-circle"></i> Bantuan & Panduan
                            </a>
                        </li>
                        @if(auth()->user()->role === 'admin')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('device*') ? 'active' : '' }}" href="{{ route('device.index') }}">
                                <i class="fas fa-microchip"></i> Manajemen Device
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users-cog"></i> Manajemen User
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
    
    <script>
        // ============ SIMPLE CLOCK (RELIABLE) ============
        function updateClock() {
            try {
                const now = new Date();
                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');
                const timeString = `${hours}:${minutes}:${seconds}`;
                
                const clockElement = document.getElementById('currentTime');
                if (clockElement) {
                    clockElement.textContent = timeString;
                }
            } catch(error) {
                console.error('Clock error:', error);
            }
        }
        
        // Start immediately
        updateClock();
        setInterval(updateClock, 1000);

        // ============ REAL-TIME INDICATORS (MEDICAL DASHBOARD) ============
        const RealtimeIndicators = {
            // DOM Elements
            tempIndicator: null,
            tempValue: null,
            humidityIndicator: null,
            humidityValue: null,
            espIndicator: null,
            espIcon: null,
            espStatus: null,
            espStatusText: null,
            deviceSelector: null,
            selectedDeviceId: null,
            pollInterval: null,
            deviceRefreshInterval: null,

            // Configuration
            config: {
                apiEndpoint: '/api/monitoring/realtime/latest',
                pollInterval: 1000, // 1 second for device data
                deviceRefreshInterval: 30000, // 30 seconds to reload devices list
                tempThresholds: {
                    normal: 30,
                    warning: 35
                },
                humidityThreshold: 60
            },

            // Initialize
            init() {
                this.cacheElements();
                if (this.elementsCached()) {
                    // Load devices first, then setup selectors
                    this.loadDevices().then(() => {
                        // Setup device selector listener
                        if (this.deviceSelector) {
                            this.deviceSelector.addEventListener('change', () => {
                                this.selectedDeviceId = this.deviceSelector.value;
                                console.log(`🔄 Device changed to: ${this.deviceSelector.options[this.deviceSelector.selectedIndex].text}`);
                                this.fetchData(); // Fetch immediately on device change
                            });
                            // Set initial device (first option)
                            this.selectedDeviceId = this.deviceSelector.value || null;
                        }
                        
                        // Fetch immediately
                        this.fetchData();
                        // Then poll every 1 second for device data
                        this.pollInterval = setInterval(() => this.fetchData(), this.config.pollInterval);
                        
                        // Reload devices list every 30 seconds (auto-detect new devices)
                        this.deviceRefreshInterval = setInterval(() => {
                            this.loadDevices();
                        }, this.config.deviceRefreshInterval);
                        
                        console.log('✅ Real-time indicators initialized with dynamic device selector');
                    });
                }
            },

            async loadDevices() {
                try {
                    const response = await fetch('/api/monitoring/devices');
                    const data = await response.json();
                    
                    if (data.success && data.data && data.data.length > 0) {
                        // Clear existing options except the first one
                        this.deviceSelector.innerHTML = '';
                        
                        // Populate dropdown dengan devices dari API
                        data.data.forEach(device => {
                            const option = document.createElement('option');
                            option.value = device.id;
                            option.textContent = device.device_name;
                            option.dataset.location = device.location;
                            this.deviceSelector.appendChild(option);
                        });
                        
                        console.log(`✅ Loaded ${data.data.length} devices from API`);
                    } else {
                        console.warn('⚠️ No devices found');
                    }
                } catch (error) {
                    console.error('❌ Error loading devices:', error);
                }
            },

            cacheElements() {
                this.tempIndicator = document.getElementById('tempIndicator');
                this.tempValue = document.getElementById('tempValue');
                this.humidityIndicator = document.getElementById('humidityIndicator');
                this.humidityValue = document.getElementById('humidityValue');
                this.espIndicator = document.getElementById('espIndicator');
                this.espIcon = document.getElementById('espIcon');
                this.espStatus = document.getElementById('espStatus');
                this.espStatusText = document.getElementById('espStatusText');
                this.deviceSelector = document.getElementById('deviceSelector');
            },

            elementsCached() {
                return this.tempIndicator && this.tempValue && 
                       this.humidityIndicator && this.humidityValue &&
                       this.espIndicator && this.espStatus &&
                       this.deviceSelector;
            },

            fetchData() {
                // Build URL with device_id parameter
                const url = this.selectedDeviceId 
                    ? `${this.config.apiEndpoint}?device_id=${this.selectedDeviceId}`
                    : this.config.apiEndpoint;

                fetch(url, {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        // API returns single device object when device_id specified,
                        // or array when no device_id. Always pass the data object.
                        const deviceData = Array.isArray(data.data) ? data.data[0] : data.data;
                        this.updateIndicators(deviceData);
                    }
                })
                .catch(error => {
                    console.warn('⚠️ Realtime fetch error:', error.message);
                    this.showOfflineState();
                });
            },

            updateIndicators(data) {
                try {
                    if (!data) {
                        this.showOfflineState();
                        return;
                    }
                    const temp = data.temperature;
                    const humidity = data.humidity;
                    const espOnline = data.esp_online;

                    // ==== UPDATE TEMPERATURE INDICATOR ====
                    if (temp !== null && temp !== undefined) {
                        this.tempValue.textContent = temp.toFixed(1) + '°C';
                        
                        // Update class based on temperature
                        this.tempIndicator.className = 'indicator-light temperature';
                        
                        if (temp > this.config.tempThresholds.warning) {
                            this.tempIndicator.classList.add('critical');
                            console.log(`🔴 TEMP CRITICAL: ${temp}°C`);
                        } else if (temp >= this.config.tempThresholds.normal) {
                            this.tempIndicator.classList.add('warning');
                            console.log(`🟠 TEMP WARNING: ${temp}°C`);
                        }
                        // else: normal (default green)
                    }

                    // ==== UPDATE HUMIDITY INDICATOR ====
                    if (humidity !== null && humidity !== undefined) {
                        this.humidityValue.textContent = Math.round(humidity) + '%';
                        
                        // Update class based on humidity
                        this.humidityIndicator.className = 'indicator-light humidity';
                        
                        if (humidity >= this.config.humidityThreshold) {
                            this.humidityIndicator.classList.add('warning');
                            console.log(`🟠 HUMIDITY WARNING: ${humidity}%`);
                        }
                        // else: normal (default cyan)
                    }

                    // ==== UPDATE ESP STATUS INDICATOR ====
                    if (espOnline) {
                        this.espIndicator.className = 'indicator-light esp-online';
                        this.espIcon.className = 'fas fa-wifi';
                        this.espStatus.textContent = 'ONLINE';
                        if (this.espStatusText) this.espStatusText.textContent = '✅ Connected';
                        console.log(`✅ ESP ONLINE`);
                    } else {
                        this.espIndicator.className = 'indicator-light esp-offline';
                        this.espIcon.className = 'fas fa-wifi-off';
                        this.espStatus.textContent = 'OFFLINE';
                        if (this.espStatusText) this.espStatusText.textContent = '⚠️ Disconnected';
                        console.log(`❌ ESP OFFLINE`);
                    }

                } catch (error) {
                    console.error('❌ Update indicators error:', error);
                    this.showOfflineState();
                }
            },

            showOfflineState() {
                // Temperature: unknown
                if (this.tempValue) this.tempValue.textContent = '--°C';
                if (this.tempIndicator) this.tempIndicator.className = 'indicator-light temperature';

                // Humidity: unknown
                if (this.humidityValue) this.humidityValue.textContent = '--%';
                if (this.humidityIndicator) this.humidityIndicator.className = 'indicator-light humidity';

                // ESP: offline
                if (this.espIndicator) this.espIndicator.className = 'indicator-light esp-offline';
                if (this.espIcon) this.espIcon.className = 'fas fa-wifi-off';
                if (this.espStatus) this.espStatus.textContent = 'OFFLINE';
                if (this.espStatusText) this.espStatusText.textContent = '❌ No data';
            },

            destroy() {
                if (this.pollInterval) {
                    clearInterval(this.pollInterval);
                    console.log('🛑 Real-time data polling stopped');
                }
                if (this.deviceRefreshInterval) {
                    clearInterval(this.deviceRefreshInterval);
                    console.log('🛑 Device list refresh stopped');
                }
            }
        };

        // Initialize on DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => RealtimeIndicators.init());
        } else {
            RealtimeIndicators.init();
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => RealtimeIndicators.destroy());
    </script>
    
    @yield('js')
</body>
</html>
