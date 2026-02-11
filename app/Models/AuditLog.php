<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'action',
        'model_name',
        'model_id',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    const ACTIONS = [
        'login' => 'Login',
        'logout' => 'Logout',
        'create' => 'Buat Data',
        'update' => 'Update Data',
        'delete' => 'Hapus Data',
        'export' => 'Export Laporan',
        'view' => 'Lihat Data',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log($action, $description, $modelName = null, $modelId = null): void
    {
        $request = request();
        self::create([
            'user_id' => auth()->id() ?? null,
            'action' => $action,
            'description' => $description,
            'model_name' => $modelName,
            'model_id' => $modelId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function getActionLabel(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    public function getActionBadgeClass(): string
    {
        return match ($this->action) {
            'login' => 'badge-success',
            'logout' => 'badge-secondary',
            'create', 'update' => 'badge-info',
            'delete' => 'badge-danger',
            'export', 'view' => 'badge-primary',
            default => 'badge-secondary',
        };
    }
}
