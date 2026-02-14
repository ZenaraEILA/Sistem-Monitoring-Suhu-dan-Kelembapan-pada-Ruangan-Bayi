<?php
/**
 * Helper untuk menghitung seconds_ago menggunakan DATABASE TIME
 * Bukan local PHP time yang bisa berbeda timezone!
 */

use Illuminate\Support\Facades\DB;

if (!function_exists('getSecondsAgoFromDatabase')) {
    /**
     * Calculate seconds ago using DATABASE SERVER time
     * 
     * @param \DateTime $recordedAt - Waktu yang dicatat
     * @return int - Detik yang lalu
     * 
     * Usage:
     * $secondsAgo = getSecondsAgoFromDatabase($monitoring->recorded_at);
     */
    function getSecondsAgoFromDatabase($recordedAt)
    {
        // Get current time from database (ensures consistency)
        $dbNow = DB::selectOne('SELECT NOW() as current_time');
        $currentTime = new \DateTime($dbNow->current_time);
        
        // Convert recorded_at to DateTime if it isn't
        if (is_string($recordedAt)) {
            $recordedAt = new \DateTime($recordedAt);
        }
        
        // Calculate difference
        $diff = $currentTime->diff($recordedAt);
        
        // Convert to seconds
        $seconds = ($diff->days * 86400) + ($diff->h * 3600) + ($diff->i * 60) + $diff->s;
        
        return $seconds;
    }
}

if (!function_exists('getDeviceStatusFromDatabase')) {
    /**
     * Get device status based on database time calculation
     * 
     * @param \App\Models\Device $device
     * @param int $timeoutSeconds - Timeout untuk OFFLINE (default: 10)
     * @return array - Status data
     */
    function getDeviceStatusFromDatabase($device, $timeoutSeconds = 10)
    {
        $latest = $device->monitorings()->latest('recorded_at')->first();
        
        if (!$latest) {
            return [
                'status' => 'unknown',
                'seconds_ago' => null,
                'display' => 'TIDAK ADA DATA',
                'color' => 'secondary'
            ];
        }
        
        $secondsAgo = getSecondsAgoFromDatabase($latest->recorded_at);
        
        if ($secondsAgo <= $timeoutSeconds) {
            $status = 'online';
            $display = 'TERHUBUNG';
            $color = 'success';
        } elseif ($secondsAgo <= 300) { // 5 menit
            $status = 'offline';
            $display = 'TIDAK TERHUBUNG';
            $color = 'warning';
        } else {
            $status = 'offline';
            $display = 'TERPUTUS LAMA';
            $color = 'danger';
        }
        
        return [
            'status' => $status,
            'seconds_ago' => $secondsAgo,
            'display' => $display,
            'color' => $color,
            'last_update' => $latest->recorded_at->toIso8601String()
        ];
    }
}
