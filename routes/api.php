<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MonitoringController;

Route::middleware('api')->prefix('monitoring')->group(function () {
    /**
     * API endpoint untuk menerima data dari ESP8266/ESP32
     * 
     * POST /api/monitoring
     * Content-Type: application/json
     * 
     * Request body:
     * {
     *   "device_id": "DEVICE_XXXXX_1234567890",
     *   "temperature": 26.5,
     *   "humidity": 55.2
     * }
     */
    Route::post('/', [MonitoringController::class, 'store']);

    /**
     * Get latest monitoring data for a device
     * 
     * GET /api/monitoring/{deviceId}
     */
    Route::get('/{deviceId}', [MonitoringController::class, 'getLatest']);
});
