<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\IncidentMarkerController;
use App\Http\Controllers\ChecklistController;
use App\Http\Controllers\DoctorNoteController;
use App\Http\Controllers\AdvancedFeatureController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\HelpController;

/**
 * Auth Routes
 */
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

/**
 * Protected Routes
 */
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Help & Guide
    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
    Route::get('/help/{section}', [HelpController::class, 'section'])->name('help.section');

    // Profile Management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('/password', [ProfileController::class, 'editPassword'])->name('profile.edit-password');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
        Route::post('/photo', [ProfileController::class, 'uploadPhoto'])->name('profile.upload-photo');
        Route::delete('/photo', [ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');
    });

    // Monitoring
    Route::prefix('monitoring')->group(function () {
        Route::get('/history', [MonitoringController::class, 'history'])->name('monitoring.history');
        Route::get('/chart', [MonitoringController::class, 'chart'])->name('monitoring.chart');
        Route::get('/hourly-trend', [MonitoringController::class, 'hourlyTrend'])->name('monitoring.hourly-trend');
        Route::post('/{id}/action', [MonitoringController::class, 'updateAction'])->name('monitoring.update-action');
        Route::get('/emergency-incidents', [MonitoringController::class, 'emergencyIncidents'])->name('monitoring.emergency-incidents');
    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/export-daily', [ReportController::class, 'exportDaily'])->name('reports.export-daily');
        Route::post('/export-weekly', [ReportController::class, 'exportWeekly'])->name('reports.export-weekly');
        Route::post('/export-monthly', [ReportController::class, 'exportMonthly'])->name('reports.export-monthly');
    });

    // Login Logs (View only for auditing)
    Route::prefix('login-logs')->middleware('admin')->group(function () {
        Route::get('/', [LoginLogController::class, 'index'])->name('login-logs.index');
    });

    // Incident Markers
    Route::prefix('incident-marker')->group(function () {
        Route::post('/', [IncidentMarkerController::class, 'store'])->name('incident-marker.store');
        Route::get('/monitoring/{monitoring}', [IncidentMarkerController::class, 'getMarkers'])->name('incident-marker.get');
        Route::delete('/{marker}', [IncidentMarkerController::class, 'destroy'])->name('incident-marker.destroy');
        Route::get('/device/{device}/chart', [IncidentMarkerController::class, 'getChartWithMarkers'])->name('incident-marker.chart');
    });

    // Daily Checklists
    Route::prefix('checklist')->group(function () {
        Route::get('/device/{device}/today', [ChecklistController::class, 'showToday'])->name('checklist.today');
        Route::put('/{checklist}', [ChecklistController::class, 'update'])->name('checklist.update');
        Route::get('/device/{device}/history', [ChecklistController::class, 'history'])->name('checklist.history');
        Route::get('/device/{device}/status', [ChecklistController::class, 'checkTodayStatus'])->name('checklist.status');
    });

    // Doctor Notes
    Route::prefix('doctor-note')->group(function () {
        Route::get('/device/{device}', [DoctorNoteController::class, 'index'])->name('doctor-note.index');
        Route::post('/', [DoctorNoteController::class, 'store'])->name('doctor-note.store');
        Route::put('/{note}', [DoctorNoteController::class, 'update'])->name('doctor-note.update');
        Route::delete('/{note}', [DoctorNoteController::class, 'destroy'])->name('doctor-note.destroy');
        Route::get('/device/{device}/range', [DoctorNoteController::class, 'getRange'])->name('doctor-note.range');
    });

    // Advanced Features
    Route::prefix('advanced')->group(function () {
        Route::get('/early-warning/{device}', [AdvancedFeatureController::class, 'getEarlyWarningPatterns'])->name('advanced.early-warning');
        Route::get('/device-status/{device}', [AdvancedFeatureController::class, 'getDeviceStatus'])->name('advanced.device-status');
        Route::get('/room-stability/{device}', [AdvancedFeatureController::class, 'getRoomStability'])->name('advanced.room-stability');
        Route::get('/response-time/{device}', [AdvancedFeatureController::class, 'getResponseTimeStats'])->name('advanced.response-time');
        Route::get('/archived/{device}', [AdvancedFeatureController::class, 'getArchivedData'])->name('advanced.archived-data');
        Route::post('/archive-old-data', [AdvancedFeatureController::class, 'archiveOldData'])->name('advanced.archive-execute')->middleware('admin');
    });

    // Print Reports
    Route::prefix('print')->group(function () {
        Route::get('/today/{device}', [PrintController::class, 'printTodayCondition'])->name('print.today');
        Route::get('/today/{device}/pdf', [PrintController::class, 'downloadPDF'])->name('print.today-pdf');
    });

    // Audit Logs (Admin only)
    Route::prefix('audit-logs')->middleware('admin')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/user/{user}', [AuditLogController::class, 'getUserLogs'])->name('audit-logs.user');
        Route::get('/summary', [AuditLogController::class, 'getActivitySummary'])->name('audit-logs.summary');
        Route::get('/export', [AuditLogController::class, 'export'])->name('audit-logs.export');
    });

    // Device Management (Admin Only)
    Route::prefix('device')->middleware('admin')->group(function () {
        Route::get('/', [DeviceController::class, 'index'])->name('device.index');
        Route::get('/create', [DeviceController::class, 'create'])->name('device.create');
        Route::post('/', [DeviceController::class, 'store'])->name('device.store');
        Route::get('/{device}/edit', [DeviceController::class, 'edit'])->name('device.edit');
        Route::put('/{device}', [DeviceController::class, 'update'])->name('device.update');
        Route::delete('/{device}', [DeviceController::class, 'destroy'])->name('device.destroy');
    });

    // User Management (Admin Only) - RBAC System
    Route::prefix('admin')->middleware('is_admin')->name('admin.')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::post('/users/{user}/update-role', [UserManagementController::class, 'updateRole'])->name('users.updateRole');
        Route::post('/users/{user}/deactivate', [UserManagementController::class, 'deactivateUser'])->name('users.deactivate');
        Route::post('/users/{user}/activate', [UserManagementController::class, 'activateUser'])->name('users.activate');
    });
});

// Redirect root to dashboard or login
Route::redirect('/', '/dashboard');