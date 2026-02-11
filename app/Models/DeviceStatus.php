<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceStatus extends Model
{
    protected $fillable = [
        'device_id',
        'last_data_at',
        'status',
        'offline_minutes',
        'checked_at',
    ];

    protected $casts = [
        'last_data_at' => 'datetime',
        'checked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function isOffline(): bool
    {
        return $this->status === 'offline';
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'online' => 'badge-success',
            'offline' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'online' => 'Online',
            'offline' => 'Offline',
            default => 'Tidak Diketahui',
        };
    }

    public static function checkDeviceStatus(Device $device): void
    {
        $lastMonitoring = $device->monitorings()->latest('created_at')->first();
        $status = $device->deviceStatus ?? new static(['device_id' => $device->id]);

        if ($lastMonitoring) {
            $minutesAgo = $lastMonitoring->created_at->diffInMinutes(now());
            
            if ($minutesAgo > 5) {
                $status->status = 'offline';
                $status->offline_minutes = $minutesAgo;
            } else {
                $status->status = 'online';
                $status->offline_minutes = 0;
            }
            
            $status->last_data_at = $lastMonitoring->created_at;
        } else {
            $status->status = 'unknown';
        }

        $status->checked_at = now();
        $status->save();
    }
}
