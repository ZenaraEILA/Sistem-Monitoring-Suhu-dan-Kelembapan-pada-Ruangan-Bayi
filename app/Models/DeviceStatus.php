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

    public static function checkDeviceStatus(Device $device, int $timeoutSeconds = 10): void
    {
        // SELALU gunakan recorded_at (waktu data dari sensor), bukan created_at (waktu disimpan DB)
        $lastMonitoring = $device->monitorings()->latest('recorded_at')->first();
        $status = $device->deviceStatus ?? new static(['device_id' => $device->id]);

        if ($lastMonitoring) {
            // Get current time from DATABASE (bukan local PHP time!)
            $dbNow = \DB::selectOne('SELECT NOW() as db_time');
            $serverTime = new \DateTime($dbNow->db_time);
            
            // Hitung selisih waktu BERDASARKAN DATABASE TIME
            $diff = $serverTime->diff($lastMonitoring->recorded_at);
            $secondsAgo = ($diff->days * 86400) + ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
            $minutesAgo = (int)($secondsAgo / 60);
            
            // Status logic: ONLINE jika data masuk dalam timeout window (default 10 detik)
            // Berikan margin 10 detik untuk network latency + processing time
            if ($secondsAgo <= $timeoutSeconds) {
                $status->status = 'online';
                $status->offline_minutes = 0;
            } else {
                $status->status = 'offline';
                $status->offline_minutes = $minutesAgo;
            }
            
            // PENTING: Update last_data_at dengan recorded_at (waktu sensor), bukan created_at
            $status->last_data_at = $lastMonitoring->recorded_at;
        } else {
            // Tidak ada data sama sekali
            $status->status = 'unknown';
            $status->offline_minutes = 0; // Set to 0 (not null) for offline_minutes
            $status->last_data_at = null;
        }

        $status->checked_at = now();
        $status->save();
    }
}
