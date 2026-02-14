<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'last_login_at',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * ✅ Boot model - ensure role defaults to 'petugas'
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (is_null($model->role)) {
                $model->role = 'petugas';
            }
        });
    }

    /**
     * Get the login logs for this user.
     */
    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }

    /**
     * Get the audit logs for this user.
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get the incident markers created by this user.
     */
    public function incidentMarkers()
    {
        return $this->hasMany(IncidentMarker::class, 'created_by');
    }

    /**
     * Get the doctor notes created by this user.
     */
    public function doctorNotes()
    {
        return $this->hasMany(DoctorNote::class, 'created_by');
    }

    /**
     * Get the daily checklists assigned to this user.
     */
    public function dailyChecklists()
    {
        return $this->hasMany(DailyChecklist::class, 'petugas_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isPetugas(): bool
    {
        return $this->role === 'petugas';
    }

    /**
     * ✅ Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    // ============================================================
    // 🔐 AUTHORIZATION METHODS
    // ============================================================

    /**
     * 🔐 Can access user management dashboard
     */
    public function canAccessUserManagement(): bool
    {
        return $this->isAdmin();
    }

    /**
     * 🔐 Can change user's role
     */
    public function canChangeRoleOf(User $targetUser): bool
    {
        // Admin tidak bisa change role dirinya sendiri
        if ($targetUser->id === $this->id) {
            return false;
        }
        return $this->isAdmin();
    }

    /**
     * 🔐 Can deactivate user
     */
    public function canDeactivateUser(User $targetUser): bool
    {
        // Tidak bisa deactivate dirinya sendiri
        if ($targetUser->id === $this->id) {
            return false;
        }
        return $this->isAdmin();
    }

    // ============================================================
    // 📝 ROLE MANAGEMENT METHODS
    // ============================================================

    /**
     * 🔐 Update role dengan validasi ketat
     * Role hanya bisa diubah melalui method ini, bukan mass assignment
     */
    public function updateRole(string $newRole): bool
    {
        if (!in_array($newRole, ['admin', 'petugas'])) {
            throw new \InvalidArgumentException("Role '{$newRole}' tidak valid. Hanya 'admin' atau 'petugas'.");
        }

        if ($this->role === $newRole) {
            throw new \InvalidArgumentException("User sudah memiliki role '{$newRole}'.");
        }

        $this->update(['role' => $newRole]);
        return true;
    }

    /**
     * 🔐 Deactivate user
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * ✅ Activate user
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * ✅ Record login activity
     */
    public function recordLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * ✅ Get human-friendly last login time
     */
    public function getLastLoginInfo(): string
    {
        if (is_null($this->last_login_at)) {
            return 'Belum pernah login';
        }
        return $this->last_login_at->diffForHumans();
    }

    /**
     * ✅ Get user role in Indonesian
     */
    public function getRoleIndonesian(): string
    {
        return match($this->role) {
            'admin' => 'Admin',
            'petugas' => 'Petugas',
            default => 'Unknown',
        };
    }

    /**
     * ✅ Get user status in Indonesian
     */
    public function getStatusIndonesian(): string
    {
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }
}
