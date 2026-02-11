<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorNote extends Model
{
    protected $fillable = [
        'device_id',
        'created_by',
        'note_date',
        'content',
        'category',
    ];

    protected $casts = [
        'note_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const CATEGORIES = [
        'general' => 'Umum',
        'observation' => 'Observasi',
        'treatment' => 'Perawatan',
        'equipment' => 'Peralatan',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCategoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? 'Umum';
    }

    public function getCategoryBadgeClass(): string
    {
        return match ($this->category) {
            'observation' => 'badge-info',
            'treatment' => 'badge-warning',
            'equipment' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
