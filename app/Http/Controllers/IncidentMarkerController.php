<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\IncidentMarker;
use App\Models\Monitoring;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class IncidentMarkerController extends Controller
{
    /**
     * Store a new incident marker
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'monitoring_id' => 'required|exists:monitorings,id',
            'description' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['marked_at'] = now();

        $marker = IncidentMarker::create($validated);

        // Log activity
        AuditLog::log('create', "Menambahkan marker incident untuk monitoring ID {$marker->monitoring_id}", 'IncidentMarker', $marker->id);

        return response()->json([
            'success' => true,
            'message' => 'Incident marker berhasil ditambahkan',
            'marker' => $marker,
        ]);
    }

    /**
     * Get incident markers for a monitoring record
     */
    public function getMarkers(Monitoring $monitoring)
    {
        $markers = $monitoring->incidentMarkers()->with('creator')->get();

        return response()->json([
            'success' => true,
            'markers' => $markers,
        ]);
    }

    /**
     * Delete incident marker
     */
    public function destroy(IncidentMarker $marker)
    {
        $this->authorize('delete', $marker);

        $markerId = $marker->id;
        $marker->delete();

        // Log activity
        AuditLog::log('delete', "Menghapus incident marker ID {$markerId}", 'IncidentMarker', $markerId);

        return response()->json([
            'success' => true,
            'message' => 'Incident marker berhasil dihapus',
        ]);
    }

    /**
     * Get chart data with markers
     */
    public function getChartWithMarkers(Device $device, Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $monitorings = $device->monitorings()
            ->whereDate('recorded_at', $date)
            ->with('incidentMarkers')
            ->get();

        $markers = IncidentMarker::whereHas('monitoring', function ($q) use ($device, $date) {
            $q->where('device_id', $device->id)
              ->whereDate('created_at', $date);
        })
        ->with('creator')
        ->get();

        return response()->json([
            'success' => true,
            'monitorings' => $monitorings,
            'markers' => $markers,
        ]);
    }
}
