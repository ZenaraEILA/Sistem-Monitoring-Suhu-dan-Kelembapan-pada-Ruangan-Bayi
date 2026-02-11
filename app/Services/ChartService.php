<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ChartService
{
    /**
     * Generate temperature and humidity chart as PNG image
     * Format: Area Chart with all data points and safety zones
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

        // Colors - Enhanced palette
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 33, 37, 41);
        $darkGray = imagecolorallocate($image, 108, 117, 125);
        $lightGray = imagecolorallocate($image, 248, 249, 250);
        $gridGray = imagecolorallocate($image, 230, 230, 230);
        
        // Temperature colors
        $redColor = imagecolorallocate($image, 220, 53, 69);      // Main line
        $redFill = imagecolorallocate($image, 255, 225, 225);     // Area fill (lighter)
        $redLight = imagecolorallocate($image, 255, 240, 240);    // Unsafe zone background
        
        // Humidity colors
        $blueColor = imagecolorallocate($image, 13, 110, 253);    // Main line
        $blueFill = imagecolorallocate($image, 220, 240, 255);    // Area fill (lighter)
        $blueLight = imagecolorallocate($image, 240, 248, 255);   // Unsafe zone background

        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        // Padding - Increased for better labels
        $leftPadding = 90;
        $rightPadding = 50;
        $topPadding = 70;
        $bottomPadding = 90;

        $graphWidth = $width - $leftPadding - $rightPadding;
        $graphHeight = $height - $topPadding - $bottomPadding;

        // Draw background rectangle
        imagefilledrectangle($image, $leftPadding, $topPadding, $width - $rightPadding, $height - $bottomPadding, $lightGray);

        // Calculate data range - Fixed range to match web interface
        $temperatures = $monitorings->pluck('temperature')->toArray();
        $humidities = $monitorings->pluck('humidity')->toArray();

        // Use fixed range like ApexCharts (12-40°C for display, 0-100% for humidity)
        $minTemp = 12;
        $maxTemp = 40;
        $tempRange = $maxTemp - $minTemp;

        $minHum = 0;
        $maxHum = 100;
        $humRange = $maxHum - $minHum;

        // Draw safety/unsafe zones first (background)
        // Temperature unsafe zones: < 15°C and > 30°C
        if ($minTemp < 15) {
            $unsafeY1Start = $topPadding + (($minTemp - $minTemp) / $tempRange) * $graphHeight;
            $unsafeY1End = $topPadding + ((15 - $minTemp) / $tempRange) * $graphHeight;
            imagefilledrectangle($image, $leftPadding, (int)$unsafeY1Start, $width - $rightPadding, (int)$unsafeY1End, $redLight);
        }
        
        $unsafeY2Start = $topPadding + ((30 - $minTemp) / $tempRange) * $graphHeight;
        $unsafeY2End = $topPadding + (($maxTemp - $minTemp) / $tempRange) * $graphHeight;
        imagefilledrectangle($image, $leftPadding, (int)$unsafeY2Start, $width - $rightPadding, (int)$unsafeY2End, $redLight);

        // Humidity unsafe zones: < 35% and > 60%
        $unsafeHumY1Start = $topPadding + ((100 - 35) / $humRange) * $graphHeight;
        $unsafeHumY1End = $topPadding + (($maxHum - $minHum) / $humRange) * $graphHeight;
        imagefilledrectangle($image, $leftPadding, (int)$unsafeHumY1Start, $width - $rightPadding, (int)$unsafeHumY1End, $blueLight);
        
        $unsafeHumY2Start = $topPadding;
        $unsafeHumY2End = $topPadding + ((35 - $minHum) / $humRange) * $graphHeight;
        imagefilledrectangle($image, $leftPadding, (int)$unsafeHumY2Start, $width - $rightPadding, (int)$unsafeHumY2End, $blueLight);

        // Draw grid lines (horizontal)
        imagesetthickness($image, 1);
        for ($i = 0; $i <= 10; $i++) {
            $y = $topPadding + ($graphHeight / 10) * $i;
            imageline($image, $leftPadding, $y, $width - $rightPadding, $y, $gridGray);
        }


        // Draw axes - Thicker for better appearance
        imagesetthickness($image, 3);
        imageline($image, $leftPadding, $topPadding, $leftPadding, $height - $bottomPadding, $black);
        imageline($image, $leftPadding, $height - $bottomPadding, $width - $rightPadding, $height - $bottomPadding, $black);

        // Draw title - Enhanced styling and positioning
        $titleText = 'Grafik Monitoring Suhu & Kelembapan Ruangan Bayi';
        imagestring($image, 5, $leftPadding + 10, 18, $titleText, $black);
        
        // Draw subtitle with gradient effect simulation
        $subtitleText = 'Dual Axis Chart | Temperature (°C) & Humidity (%)';
        imagestring($image, 2, $leftPadding + 10, 35, $subtitleText, $darkGray);

        // Draw Y-axis labels - Temperature (Left)
        $fontSize = 2;
        for ($i = 0; $i <= 10; $i++) {
            $temp = $maxTemp - ($tempRange / 10) * $i;
            $y = $topPadding + ($graphHeight / 10) * $i;
            imagestring($image, $fontSize, $leftPadding - 85, $y - 8, round($temp, 1) . '°C', $redColor);
            imagesetthickness($image, 1);
            imageline($image, $leftPadding - 5, (int)$y, $leftPadding, (int)$y, $darkGray);
        }
        
        // Draw Y-axis labels - Humidity (Right) with better spacing
        for ($i = 0; $i <= 10; $i++) {
            $hum = $maxHum - ($humRange / 10) * $i;
            $y = $topPadding + ($graphHeight / 10) * $i;
            imagestring($image, $fontSize, $width - $rightPadding + 10, $y - 8, round($hum, 0) . '%', $blueColor);
            imagesetthickness($image, 1);
            imageline($image, $width - $rightPadding, (int)$y, $width - $rightPadding + 5, (int)$y, $darkGray);
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
            
            // Temperature Y coordinate (using fixed range)
            $yTemp = $height - $bottomPadding - (($monitoring->temperature - $minTemp) / $tempRange) * $graphHeight;
            
            // Humidity Y coordinate (scaled to full 0-100%)
            $yHum = $height - $bottomPadding - (($monitoring->humidity - $minHum) / $humRange) * $graphHeight;
            
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

            // Draw temperature line - Thicker
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

        // Draw data point dots - Larger and more visible
        imagesetthickness($image, 1);
        for ($i = 0; $i < count($points); $i++) {
            // Temperature points - Larger red dots
            imagefilledarc($image, (int)$points[$i]['x'], (int)$points[$i]['temp'], 6, 6, 0, 360, $redColor, IMG_ARC_PIE);
            // Add white border
            imagearc($image, (int)$points[$i]['x'], (int)$points[$i]['temp'], 6, 6, 0, 360, $white);
            
            // Humidity points - Larger blue dots
            imagefilledarc($image, (int)$points[$i]['x'], (int)$points[$i]['hum'], 6, 6, 0, 360, $blueColor, IMG_ARC_PIE);
            // Add white border
            imagearc($image, (int)$points[$i]['x'], (int)$points[$i]['hum'], 6, 6, 0, 360, $white);
        }

        // Draw X-axis time labels (show every nth label to avoid crowding)
        $labelStep = max(1, intval($pointCount / 12)); // Show ~12 labels max
        $fontSize = 1;
        for ($i = 0; $i < count($points); $i += $labelStep) {
            $labelX = (int)$points[$i]['x'] - 20;
            $labelY = $height - $bottomPadding + 12;
            imagestring($image, $fontSize, $labelX, $labelY, $points[$i]['time'], $darkGray);
        }

        // Draw axis labels - more prominent
        imagestring($image, 3, $width - 140, $height - 35, 'Waktu (Jam)', $darkGray);
        
        // Left Y-axis label (Temperature)
        imagestring($image, 2, 5, $topPadding - 5, 'SUHU', $redColor);
        
        // Right Y-axis label (Humidity)
        imagestring($image, 2, $width - 40, $topPadding - 5, 'KELEMBAPAN', $blueColor);

        // Draw legend with modern styling - Dual Axis Legend
        $legendX = $width - 400;
        $legendY = $topPadding + 15;
        $legendBoxWidth = 200;
        $legendBoxHeight = 70;

        // Legend background with border
        imagefilledrectangle($image, $legendX - 5, $legendY - 5, $legendX + $legendBoxWidth, $legendY + $legendBoxHeight, $white);
        imagerectangle($image, $legendX - 5, $legendY - 5, $legendX + $legendBoxWidth, $legendY + $legendBoxHeight, $gridGray);
        imagesetthickness($image, 2);
        imageline($image, $legendX - 5, $legendY - 5, $legendX + $legendBoxWidth, $legendY - 5, $redColor);

        // Dual Axis Label
        imagestring($image, 3, $legendX + 5, $legendY + 1, 'DUAL AXIS CHART', $black);
        
        // Temperature legend (Left Axis)
        imagefilledrectangle($image, $legendX + 5, $legendY + 18, $legendX + 17, $legendY + 30, $redColor);
        imagestring($image, 2, $legendX + 22, $legendY + 18, 'Suhu (°C)', $redColor);

        // Humidity legend (Right Axis)
        imagefilledrectangle($image, $legendX + 5, $legendY + 35, $legendX + 17, $legendY + 47, $blueColor);
        imagestring($image, 2, $legendX + 22, $legendY + 35, 'Kelembapan (%)', $blueColor);

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
