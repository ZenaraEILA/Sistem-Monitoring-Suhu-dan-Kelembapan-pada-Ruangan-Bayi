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
     * Get latest monitoring data for a device
     * 
     * GET /api/monitoring/{deviceId}
     */
    Route::get('/{deviceId}', [MonitoringController::class, 'getLatest']);
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
