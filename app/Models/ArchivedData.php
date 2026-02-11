<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivedData extends Model
{
    protected $fillable = [
        'device_id',
        'archive_date',
        'record_count',
        'summary',
        'data',
        'archived_at',
    ];

    protected $casts = [
        'summary' => 'json',
        'data' => 'json',
        'archive_date' => 'date',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function getSummary()
    {
        return $this->summary ?? [
            'avg_temp' => 0,
            'max_temp' => 0,
            'min_temp' => 0,
            'avg_humidity' => 0,
            'incidents_count' => 0,
        ];
    }

    public static function archiveOldData($daysOld = 30)
    {
        $cutoffDate = now()->subDays($daysOld)->startOfDay();

        // Get devices yang memiliki data lama
        Device::chunk(10, function ($devices) use ($cutoffDate) {
            foreach ($devices as $device) {
                // Group data by date dan archive
                $oldData = $device->monitorings()
                    ->where('created_at', '<', $cutoffDate)
                    ->get()
                    ->groupBy(fn($m) => $m->created_at->format('Y-m-d'));

                foreach ($oldData as $date => $records) {
                    $existingArchive = self::where('device_id', $device->id)
                        ->where('archive_date', $date)
                        ->first();

                    if (!$existingArchive) {
                        $summary = [
                            'avg_temp' => $records->avg('temperature'),
                            'max_temp' => $records->max('temperature'),
                            'min_temp' => $records->min('temperature'),
                            'avg_humidity' => $records->avg('humidity'),
                            'incidents_count' => $records->where('status', 'unsafe')->count(),
                        ];

                        self::create([
                            'device_id' => $device->id,
                            'archive_date' => $date,
                            'record_count' => $records->count(),
                            'summary' => $summary,
                            'data' => $records->toJson(),
                            'archived_at' => now(),
                        ]);

                        // Delete dari main table
                        $records->each->delete();
                    }
                }
            }
        });
    }
}
