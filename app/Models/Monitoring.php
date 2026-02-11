<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    protected $fillable = [
        'device_id',
        'temperature',
        'humidity',
        'status',
        'recorded_at',
        'action_note',
        'consecutive_unsafe_count',
        'is_emergency',
        'unsafe_detected_at',
        'action_taken_at',
        'response_time_minutes',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'unsafe_detected_at' => 'datetime',
        'action_taken_at' => 'datetime',
        'is_emergency' => 'boolean',
    ];

    protected $appends = ['recommendation', 'action_required'];

    /**
     * Get the device that owns this monitoring record.
     */
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Get incident markers for this monitoring record.
     */
    public function incidentMarkers()
    {
        return $this->hasMany(IncidentMarker::class);
    }

    /**
     * Get recommendation text based on current temperature and humidity
     */
    public function getRecommendationAttribute()
    {
        $recommendations = [];
        
        if ($this->temperature > 30) {
            $recommendations[] = "🌡️ Suhu terlalu tinggi (>30°C) - Turunkan AC";
        } elseif ($this->temperature < 15) {
            $recommendations[] = "❄️ Suhu terlalu rendah (<15°C) - Naikkan suhu AC / tutup ventilasi";
        }

        if ($this->humidity > 60) {
            $recommendations[] = "💨 Kelembapan terlalu tinggi (>60%) - Nyalakan ventilasi / dehumidifier";
        } elseif ($this->humidity < 35) {
            $recommendations[] = "🏜️ Kelembapan terlalu rendah (<35%) - Kurangi penggunaan pendingin";
        }

        return !empty($recommendations) ? implode(' | ', $recommendations) : "✅ Kondisi normal, tidak perlu tindakan";
    }

    /**
     * Check if action is required
     */
    public function getActionRequiredAttribute()
    {
        return $this->status === 'Tidak Aman';
    }

    /**
     * Get summary of recommendations in safe format
     */
    public function getRecommendationListAttribute()
    {
        $recommendations = [];
        
        if ($this->temperature > 30) {
            $recommendations[] = "Turunkan suhu AC";
        } elseif ($this->temperature < 15) {
            $recommendations[] = "Naikkan suhu AC / tutup ventilasi";
        }

        if ($this->humidity > 60) {
            $recommendations[] = "Nyalakan ventilasi / dehumidifier";
        } elseif ($this->humidity < 35) {
            $recommendations[] = "Kurangi penggunaan pendingin";
        }

        return $recommendations;
    }

    /**
     * Check if this monitoring data represents an emergency condition
     * (unsafe condition for more than 5 minutes)
     */
    public static function checkEmergencyCondition($deviceId)
    {
        $fiveMinutesAgo = Carbon::now()->subMinutes(5);
        
        $unsafeCount = self::where('device_id', $deviceId)
            ->where('status', 'Tidak Aman')
            ->where('recorded_at', '>=', $fiveMinutesAgo)
            ->count();
        
        // Assuming data comes every 1 minute, 5+ records = 5 minutes
        return $unsafeCount >= 5;
    }

    /**
     * Get the latest unsafe condition details
     */
    public static function getLatestUnsafeDetails($deviceId)
    {
        return self::where('device_id', $deviceId)
            ->where('status', 'Tidak Aman')
            ->latest('recorded_at')
            ->first();
    }

    /**
     * Count unsafe conditions in the last hour
     */
    public static function countUnsafeToday($deviceId)
    {
        $today = Carbon::today();
        
        return self::where('device_id', $deviceId)
            ->where('status', 'Tidak Aman')
            ->whereDate('recorded_at', $today)
            ->count();
    }

    /**
     * Get hourly data for chart
     */
    public static function getHourlyData($deviceId, $date = null)
    {
        if (!$date) {
            $date = Carbon::today();
        }

        return self::where('device_id', $deviceId)
            ->whereDate('recorded_at', $date)
            ->selectRaw('HOUR(recorded_at) as hour')
            ->selectRaw('ROUND(AVG(temperature), 2) as avg_temp')
            ->selectRaw('ROUND(AVG(humidity), 2) as avg_humidity')
            ->selectRaw('MAX(temperature) as max_temp')
            ->selectRaw('MIN(temperature) as min_temp')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
    }
}
