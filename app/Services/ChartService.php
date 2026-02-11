<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Intervention\Image\Facades\Image;

class ChartService
{
    /**
     * Generate temperature and humidity chart as PNG image
     */
    public static function generateMonitoringChart(Collection $monitorings): string
    {
        // Group data by hour
        $chartData = [];
        $labels = [];
        $temperatures = [];
        $humidities = [];

        // Get monitoring data grouped by hour
        $grouped = $monitorings->groupBy(function ($item) {
            return $item->recorded_at->format('H:00');
        });

        foreach ($grouped as $hour => $records) {
            $labels[] = $hour;
            $temperatures[] = round($records->avg('temperature'), 2);
            $humidities[] = round($records->avg('humidity'), 2);
        }

        // If no data, return empty chart
        if (empty($labels)) {
            return ''; // Return empty string if no data
        }

        // Use Chart library or generate text-based representation
        // For now, we'll generate a simple chart using HTML/CSS rendered as image
        return self::generateChartImage($labels, $temperatures, $humidities);
    }

    /**
     * Generate status distribution chart
     */
    public static function generateStatusChart(Collection $monitorings): string
    {
        $safeCount = $monitorings->where('status', 'Aman')->count();
        $unsafeCount = $monitorings->where('status', 'Tidak Aman')->count();

        return self::generatePieChart($safeCount, $unsafeCount);
    }

    /**
     * Generate chart image using simple canvas approach
     */
    private static function generateChartImage($labels, $temperatures, $humidities)
    {
        // Create 800x400 image
        $width = 800;
        $height = 400;
        $image = imagecreatetruecolor($width, $height);

        // Colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $lightGray = imagecolorallocate($image, 240, 240, 240);
        $redColor = imagecolorallocate($image, 220, 53, 69);
        $blueColor = imagecolorallocate($image, 13, 110, 253);
        $gridColor = imagecolorallocate($image, 200, 200, 200);

        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        // Calculate scaling
        $maxTemp = max($temperatures) <= 35 ? 40 : max($temperatures) + 5;
        $minTemp = min($temperatures) >= 20 ? 15 : min($temperatures) - 5;
        $tempRange = $maxTemp - $minTemp;

        $padding = 60;
        $graphWidth = $width - $padding * 2;
        $graphHeight = $height - $padding * 2;

        // Draw grid
        imagesetthickness($image, 1);
        for ($i = 0; $i <= 10; $i++) {
            $y = $padding + ($graphHeight / 10) * $i;
            imageline($image, $padding, $y, $width - $padding, $y, $gridColor);
        }

        // Draw axes
        imagesetthickness($image, 2);
        imageline($image, $padding, $padding, $padding, $height - $padding, $black);
        imageline($image, $padding, $height - $padding, $width - $padding, $height - $padding, $black);

        // Draw Y-axis labels (temperature)
        imagesetthickness($image, 1);
        $fontSize = 2;
        for ($i = 0; $i <= 5; $i++) {
            $temp = $maxTemp - ($tempRange / 5) * $i;
            $y = $padding + ($graphHeight / 5) * $i;
            imagestring($image, $fontSize, $padding - 40, $y - 7, round($temp, 1) . '°C', $black);
            imageline($image, $padding - 3, $y, $padding, $y, $black);
        }

        // Draw data points and lines
        $pointCount = count($labels);
        if ($pointCount > 1) {
            $xStep = $graphWidth / ($pointCount - 1);

            // Draw temperature line
            imagesetthickness($image, 2);
            for ($i = 0; $i < $pointCount - 1; $i++) {
                $x1 = $padding + ($xStep * $i);
                $y1 = $height - $padding - (($temperatures[$i] - $minTemp) / $tempRange) * $graphHeight;

                $x2 = $padding + ($xStep * ($i + 1));
                $y2 = $height - $padding - (($temperatures[$i + 1] - $minTemp) / $tempRange) * $graphHeight;

                imageline($image, $x1, $y1, $x2, $y2, $redColor);
            }

            // Draw humidity line
            for ($i = 0; $i < $pointCount - 1; $i++) {
                $x1 = $padding + ($xStep * $i);
                $y1 = $height - $padding - ($humidities[$i] / 100) * $graphHeight;

                $x2 = $padding + ($xStep * ($i + 1));
                $y2 = $height - $padding - ($humidities[$i + 1] / 100) * $graphHeight;

                imageline($image, $x1, $y1, $x2, $y2, $blueColor);
            }

            // Draw points
            for ($i = 0; $i < $pointCount; $i++) {
                $x = $padding + ($xStep * $i);
                $y = $height - $padding - (($temperatures[$i] - $minTemp) / $tempRange) * $graphHeight;
                imagefilledarc($image, $x, $y, 6, 6, 0, 360, $redColor, IMG_ARC_PIE);

                $y = $height - $padding - ($humidities[$i] / 100) * $graphHeight;
                imagefilledarc($image, $x, $y, 6, 6, 0, 360, $blueColor, IMG_ARC_PIE);

                // Draw X-axis labels (every nth label to avoid crowding)
                if ($i % max(1, intval($pointCount / 8)) == 0) {
                    $labelX = $padding - 30;
                    $labelY = $height - $padding + 10;
                    imagestring($image, $fontSize, $x - 15, $labelY, $labels[$i], $black);
                }
            }
        }

        // Add title
        imagestring($image, 3, $width / 2 - 80, 20, 'Grafik Monitoring Suhu & Kelembapan', $black);

        // Add legend
        $legendY = 25;
        imageline($image, $padding + 15, $legendY, $padding + 25, $legendY, $redColor);
        imagestring($image, 2, $padding + 30, $legendY, 'Suhu (°C)', $redColor);

        imageline($image, $padding + 15, $legendY + 25, $padding + 25, $legendY + 25, $blueColor);
        imagestring($image, 2, $padding + 30, $legendY + 20, 'Kelembapan (%)', $blueColor);

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
