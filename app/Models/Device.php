<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'device_name',
        'location',
        'device_id',
        'stability_status',
        'stability_score',
        'early_warning_patterns',
        'last_stability_check',
        'ac_enabled',
        'ac_set_point',
        'ac_status',
        'ac_min_temp',
        'ac_max_temp',
        'ac_api_url',
        'ac_api_key',
    ];

    protected $casts = [
        'early_warning_patterns' => 'json',
        'last_stability_check' => 'datetime',
        'ac_enabled' => 'boolean',
        'ac_status' => 'boolean',
    ];

    /**
     * Get the monitorings for this device.
     */
    public function monitorings()
    {
        return $this->hasMany(Monitoring::class);
    }

    /**
     * Get the device status.
     */
    public function deviceStatus()
    {
        return $this->hasOne(DeviceStatus::class);
    }

    /**
     * Get the daily checklists.
     */
    public function dailyChecklists()
    {
        return $this->hasMany(DailyChecklist::class);
    }

    /**
     * Get the doctor notes.
     */
    public function doctorNotes()
    {
        return $this->hasMany(DoctorNote::class);
    }

    /**
     * Get the AC control logs for this device.
     */
    public function acLogs()
    {
        return $this->hasMany(AcLog::class);
    }

    /**
     * Get the archived data.
     */
    public function archivedData()
    {
        return $this->hasMany(ArchivedData::class);
    }

    public function isOnline(): bool
    {
        return $this->deviceStatus?->isOnline() ?? false;
    }

    public function isOffline(): bool
    {
        return $this->deviceStatus?->isOffline() ?? false;
    }

    public function getStabilityBadgeClass(): string
    {
        return match ($this->stability_status) {
            'stable' => 'badge-success',
            'unstable' => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    public function getStabilityLabel(): string
    {
        return match ($this->stability_status) {
            'stable' => 'Stabil',
            'unstable' => 'Tidak Stabil',
            default => 'Tidak Diketahui',
        };
    }
}
