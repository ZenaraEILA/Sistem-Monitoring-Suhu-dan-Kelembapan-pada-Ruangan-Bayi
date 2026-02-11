<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcLog extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'action',
        'ac_set_point',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the device that was controlled
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
