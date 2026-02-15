<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Show help & guide page
     */
    public function index()
    {
        $pageData = [
            'title' => 'Bantuan & Panduan Sistem',
            'description' => 'Panduan lengkap penggunaan Sistem Monitoring Suhu & Kelembapan',
            'lastUpdated' => '2026-02-15',
        ];

        return view('help.index', $pageData);
    }

    /**
     * Show specific help section
     */
    public function section($section)
    {
        $sections = [
            'dashboard' => 'Dashboard',
            'status' => 'Status Device',
            'history' => 'Data & Riwayat',
            'export' => 'Export PDF',
            'users' => 'Manajemen User',
        ];

        if (!array_key_exists($section, $sections)) {
            abort(404, 'Bagian tidak ditemukan');
        }

        return view('help.section', [
            'section' => $section,
            'title' => $sections[$section],
        ]);
    }
}
?>
