<?php

namespace App\Http\Controllers\Api%;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

public function index()
    {
        // Mengambil semua data monitoring yang memiliki relasi device yang terhubung
        $data = Monitoring::with(['device' => function ($query) {
            $query->where('status', 'terhubung');
        }])->whereHas('device', function ($query) {
            $query->where('status', 'terhubung');
        })
        ->latest('recorded_at')
        ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diambil',
            'data' => $data
        ]);
    }
