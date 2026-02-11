<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ChartService
{
    /**
     * Generate temperature and humidity chart as PNG image
     * Format: Area Chart with all data points
     */
    public static function generateMonitoringChart(Collection $monitorings): string
    {
        if ($monitorings->isEmpty()) {
            return '';
        }

        // Chart dimensions
        $width = 1200;
        $height = 500;
        $image = imagecreatetruecolor($width, $height);

        // Colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $lightGray = imagecolorallocate($image, 245, 245, 245);
        $gridGray = imagecolorallocate($image, 220, 220, 220);
        $redColor = imagecolorallocate($image, 220, 53, 69);      // Suhu (Temperature)
        $redFill = imagecolorallocate($image, 255, 200, 200);     // Suhu Fill (translucent)
        $blueColor = imagecolorallocate($image, 13, 110, 253);    // Kelembapan (Humidity)
        $blueFill = imagecolorallocate($image, 173, 216, 230);    // Kelembapan Fill (translucent)

        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        // Padding
        $leftPadding = 80;
        $rightPadding = 40;
        $topPadding = 60;
        $bottomPadding = 80;

        $graphWidth = $width - $leftPadding - $rightPadding;
        $graphHeight = $height - $topPadding - $bottomPadding;

        // Draw background rectangle
        imagefilledrectangle($image, $leftPadding, $topPadding, $width - $rightPadding, $height - $bottomPadding, $lightGray);

        // Calculate data range
        $temperatures = $monitorings->pluck('temperature')->toArray();
        $humidities = $monitorings->pluck('humidity')->toArray();

        $maxTemp = max($temperatures);
        $minTemp = min($temperatures);
        $tempRange = max($maxTemp - $minTemp, 1);

        $maxHum = max($humidities);
        $minHum = min($humidities);
        $humRange = max($maxHum - $minHum, 1);

        // Add margin to ranges
        $maxTemp += $tempRange * 0.1;
        $minTemp -= $tempRange * 0.1;
        $tempRange = $maxTemp - $minTemp;

        $maxHum += $humRange * 0.1;
        $minHum -= $humRange * 0.1;
        $humRange = $maxHum - $minHum;

        // Draw grid lines (horizontal)
        imagesetthickness($image, 1);
        for ($i = 0; $i <= 10; $i++) {
            $y = $topPadding + ($graphHeight / 10) * $i;
            imageline($image, $leftPadding, $y, $width - $rightPadding, $y, $gridGray);
        }

        // Draw axes
        imagesetthickness($image, 2);
        imageline($image, $leftPadding, $topPadding, $leftPadding, $height - $bottomPadding, $black);
        imageline($image, $leftPadding, $height - $bottomPadding, $width - $rightPadding, $height - $bottomPadding, $black);

        // Draw title
        imagestring($image, 5, $leftPadding + 10, 20, 'Grafik Monitoring Suhu & Kelembapan Ruangan', $black);

        // Draw Y-axis labels (Temperature on left)
        $fontSize = 2;
        for ($i = 0; $i <= 5; $i++) {
            $temp = $maxTemp - ($tempRange / 5) * $i;
            $y = $topPadding + ($graphHeight / 5) * $i;
            imagestring($image, $fontSize, $leftPadding - 75, $y - 7, round($temp, 1) . '°C', $redColor);
            imageline($image, $leftPadding - 3, $y, $leftPadding, $y, $black);
        }

        // Get data points
        $pointCount = count($monitorings);
        if ($pointCount < 2) {
            return '';
        }

        $xStep = $graphWidth / ($pointCount - 1);
        $points = [];
        $labels = [];

        // Calculate points for each monitoring record
        foreach ($monitorings as $index => $monitoring) {
            $x = $leftPadding + ($xStep * $index);
            
            // Temperature Y coordinate
            $yTemp = $height - $bottomPadding - (($monitoring->temperature - $minTemp) / $tempRange) * $graphHeight;
            
            // Humidity Y coordinate (scale to same height)
            $yHum = $height - $bottomPadding - (($monitoring->humidity / 100) * $graphHeight);
            
            $points[$index] = [
                'x' => $x,
                'temp' => $yTemp,
                'hum' => $yHum,
                'time' => $monitoring->recorded_at->format('H:i'),
            ];
            
            $labels[$index] = $monitoring->recorded_at->format('H:i');
        }

        // Draw area chart for Temperature (red fill)
        if ($pointCount > 1) {
            // Create polygon for temperature area
            $tempPolygon = [];
            foreach ($points as $point) {
                $tempPolygon[] = $point['x'];
                $tempPolygon[] = $point['temp'];
            }
            // Add bottom line points (for area fill)
            for ($i = count($points) - 1; $i >= 0; $i--) {
                $tempPolygon[] = $points[$i]['x'];
                $tempPolygon[] = $height - $bottomPadding;
            }

            imagefilledpolygon($image, $tempPolygon, count($tempPolygon) / 2, $redFill);

            // Draw temperature line
            imagesetthickness($image, 2);
            for ($i = 0; $i < count($points) - 1; $i++) {
                imageline($image, $points[$i]['x'], $points[$i]['temp'], 
                         $points[$i + 1]['x'], $points[$i + 1]['temp'], $redColor);
            }
        }

        // Draw area chart for Humidity (blue fill)
        if ($pointCount > 1) {
            // Create polygon for humidity area
            $humPolygon = [];
            foreach ($points as $point) {
                $humPolygon[] = $point['x'];
                $humPolygon[] = $point['hum'];
            }
            // Add bottom line points (for area fill)
            for ($i = count($points) - 1; $i >= 0; $i--) {
                $humPolygon[] = $points[$i]['x'];
                $humPolygon[] = $height - $bottomPadding;
            }

            imagefilledpolygon($image, $humPolygon, count($humPolygon) / 2, $blueFill);

            // Draw humidity line
            imagesetthickness($image, 2);
            for ($i = 0; $i < count($points) - 1; $i++) {
                imageline($image, $points[$i]['x'], $points[$i]['hum'], 
                         $points[$i + 1]['x'], $points[$i + 1]['hum'], $blueColor);
            }
        }

        // Draw data point dots
        imagesetthickness($image, 1);
        for ($i = 0; $i < count($points); $i++) {
            // Temperature points
            imagefilledarc($image, $points[$i]['x'], $points[$i]['temp'], 4, 4, 0, 360, $redColor, IMG_ARC_PIE);
            
            // Humidity points
            imagefilledarc($image, $points[$i]['x'], $points[$i]['hum'], 4, 4, 0, 360, $blueColor, IMG_ARC_PIE);
        }

        // Draw X-axis time labels (show every nth label to avoid crowding)
        $labelStep = max(1, intval($pointCount / 15)); // Show ~15 labels max
        $fontSize = 1;
        for ($i = 0; $i < count($points); $i += $labelStep) {
            $labelX = $points[$i]['x'] - 15;
            $labelY = $height - $bottomPadding + 10;
            imagestring($image, $fontSize, $labelX, $labelY, $points[$i]['time'], $black);
        }

        // Draw axis labels
        imagestring($image, 3, $width - 150, $height - 30, 'Waktu (Jam)', $black);
        imagestring($image, 3, 10, $topPadding - 20, 'Suhu (°C) & Kelembapan (%)', $black);

        // Draw legend
        $legendX = $width - 350;
        $legendY = $topPadding + 10;

        // Temperature legend
        imagefilledrectangle($image, $legendX, $legendY, $legendX + 15, $legendY + 15, $redColor);
        imagestring($image, 2, $legendX + 20, $legendY + 2, 'Suhu (°C)', $redColor);

        // Humidity legend
        imagefilledrectangle($image, $legendX, $legendY + 20, $legendX + 15, $legendY + 35, $blueColor);
        imagestring($image, 2, $legendX + 20, $legendY + 22, 'Kelembapan (%)', $blueColor);

        // Save image
        $path = storage_path('app/public/charts/');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $filename = 'chart_' . time() . '_' . random_int(1000, 9999) . '.png';
        imagepng($image, $path . $filename);
        imagedestroy($image);

        return $path . $filename;
    }

    /**
     * Generate status distribution chart (Pie Chart)
     */
    public static function generateStatusChart(Collection $monitorings): string
    {
        if ($monitorings->isEmpty()) {
            return '';
        }

        $safeCount = $monitorings->where('status', 'Aman')->count();
        $unsafeCount = $monitorings->where('status', 'Tidak Aman')->count();

        return self::generatePieChart($safeCount, $unsafeCount);
    }

    /**
     * Generate pie chart for status
     */
    private static function generatePieChart($safe, $unsafe)
    {
        $total = $safe + $unsafe;

        if ($total == 0) {
            return '';
        }

        $safePercent = ($safe / $total) * 100;
        $unsafePercent = ($unsafe / $total) * 100;

        $width = 300;
        $height = 300;
        $image = imagecreatetruecolor($width, $height);

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $green = imagecolorallocate($image, 40, 167, 69);
        $red = imagecolorallocate($image, 220, 53, 69);

        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        $centerX = $width / 2;
        $centerY = $height / 2;
        $radius = 80;

        // Draw pie slices
        $safeAngle = ($safePercent / 100) * 360;
        imagefilledarc($image, $centerX, $centerY, $radius * 2, $radius * 2, 0, $safeAngle, $green, IMG_ARC_PIE);
        imagefilledarc($image, $centerX, $centerY, $radius * 2, $radius * 2, $safeAngle, 360, $red, IMG_ARC_PIE);

        // Add labels
        imagestring($image, 2, $centerX - 40, 30, 'Status Distribusi', $black);
        imagestring($image, 2, $centerX - 50, $centerY + 120, 'Aman: ' . $safe . ' (' . round($safePercent, 1) . '%)', $green);
        imagestring($image, 2, $centerX - 50, $centerY + 135, 'Tidak Aman: ' . $unsafe . ' (' . round($unsafePercent, 1) . '%)', $red);

        $path = storage_path('app/public/charts/');
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $filename = 'status_' . time() . '_' . random_int(1000, 9999) . '.png';
        imagepng($image, $path . $filename);
        imagedestroy($image);

        return $path . $filename;
    }
}
