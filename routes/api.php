<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\AcControlController;

Route::middleware('api')->prefix('monitoring')->group(function () {
    /**
     * API endpoint untuk menerima data dari ESP8266/ESP32
     * 
     * POST /api/monitoring atau POST /api/monitoring/store
     * Content-Type: application/json
     * 
     * Request body:
     * {
     *   "device_id": 1,
     *   "temperature": 26.5,
     *   "humidity": 55.2
     * }
     */
    Route::post('/', [MonitoringController::class, 'store']);
    Route::post('/store', [MonitoringController::class, 'store']); // Alias untuk ESP8266

    /**
     * Get all latest monitoring data for realtime dashboard
     * Includes ESP connection status (based on last ping)
     * 
     * GET /api/monitoring/dashboard/realtime
     * HARUS SEBELUM /{deviceId} agar tidak ter-catch parameter routing
     */
    Route::get('/dashboard/realtime', [MonitoringController::class, 'getRealtimeDashboard']);

    /**
     * Get real-time latest data for live indicators (update every 1 detik)
     * 
     * GET /api/monitoring/realtime/latest
     * Response: Device data dengan ESP status (ONLINE < 5sec, OFFLINE >= 5sec)
     */
    Route::get('/realtime/latest', [MonitoringController::class, 'getRealtimeLatest']);

    /**
     * Get all devices untuk auto-populate device selector
     * Endpoint ini akan otomatis menampilkan device baru yang ditambahkan
     * 
     * GET /api/monitoring/devices
     */
    Route::get('/devices', [MonitoringController::class, 'getAllDevices']);

    /**
     * Get hourly chart data (real-time)
     * OPSI LAMA: Selalu 24 jam penuh
     * 
     * GET /api/monitoring/hourly-chart?device_id=1&date=2026-02-14
     */
    Route::get('/hourly-chart', [MonitoringController::class, 'getHourlyChartData']);

    /**
     * Get hourly chart data DYNAMIC (OPSI BARU 1 - Real-time murni)
     * Hanya jam yang memiliki data, NO padding 00:00
     * 
     * GET /api/monitoring/hourly-chart/dynamic?device_id=1&date=2026-02-14
     */
    Route::get('/hourly-chart/dynamic', [MonitoringController::class, 'getHourlyChartDataDynamic']);

    /**
     * Get latest monitoring data for a device
     * 
     * GET /api/monitoring/{deviceId}
     */
    Route::get('/{deviceId}', [MonitoringController::class, 'getLatest']);

    /**
     * ===== SIMPLE REAL-TIME POLLING ENDPOINTS =====
     * Digunakan untuk polling real-time dari dashboard
     * Response time: < 100ms untuk getLatestSimple, < 200ms untuk getChartDataSimple
     */

    /**
     * Get latest data untuk real-time indicators (temperature, humidity, status)
     * Update setiap 2 detik di frontend
     * 
     * GET /api/monitoring/get-latest?device_id=1
     */
    Route::get('/get-latest', [MonitoringController::class, 'getLatestSimple']);

    /**
     * Get chart data untuk real-time chart update
     * Update setiap 5 detik di frontend
     * 
     * GET /api/monitoring/get-chart-data?device_id=1&timeframe=1_hour
     * Timeframe options: 10_min, 30_min, 1_hour, 6_hours, 12_hours, 1_day
     */
    Route::get('/get-chart-data', [MonitoringController::class, 'getChartDataSimple']);
});

/**
 * AC Control API Routes
 * Memerlukan authentikasi (middleware auth:sanctum)
 */
Route::middleware('auth:sanctum')->prefix('ac-control')->group(function () {
    /**
     * Increase AC temperature
     * POST /api/ac-control/increase
     */
    Route::post('/increase', [AcControlController::class, 'increase']);

    /**
     * Decrease AC temperature
     * POST /api/ac-control/decrease
     */
    Route::post('/decrease', [AcControlController::class, 'decrease']);

    /**
     * Turn ON AC
     * POST /api/ac-control/turn-on
     */
    Route::post('/turn-on', [AcControlController::class, 'turnOn']);

    /**
     * Turn OFF AC
     * POST /api/ac-control/turn-off
     */
    Route::post('/turn-off', [AcControlController::class, 'turnOff']);

    /**
     * Get AC status
     * GET /api/ac-control/status
     */
    Route::get('/status', [AcControlController::class, 'getStatus']);

    /**
     * Get AC control logs
     * GET /api/ac-control/logs
     */
    Route::get('/logs', [AcControlController::class, 'getLogs']);
});
