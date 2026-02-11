<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyChecklist extends Model
{
    protected $fillable = [
        'device_id',
        'petugas_id',
        'checklist_date',
        'items',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'items' => 'json',
        'checklist_date' => 'date',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function getCompletionPercentage(): int
    {
        $items = $this->items ?? [];
        if (empty($items)) {
            return 0;
        }

        $completed = collect($items)->filter(fn($value) => $value === true)->count();
        return (int) (($completed / count($items)) * 100);
    }
}
