<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginLog extends Model
{
    protected $fillable = [
        'user_id',
        'login_time',
        'ip_address',
    ];

    protected $casts = [
        'login_time' => 'datetime',
    ];

    /**
     * Get the user that owns this login log.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
