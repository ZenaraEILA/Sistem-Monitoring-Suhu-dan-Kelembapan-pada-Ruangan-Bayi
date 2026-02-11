<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DoctorNote;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class DoctorNoteController extends Controller
{
    /**
     * Get notes for a specific date and device
     */
    public function index(Device $device, Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $category = $request->input('category', null);

        $query = DoctorNote::where('device_id', $device->id)
            ->where('note_date', $date);

        if ($category) {
            $query->where('category', $category);
        }

        $notes = $query->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'notes' => $notes,
            'date' => $date,
        ]);
    }

    /**
     * Store a new doctor note
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'note_date' => 'required|date',
            'content' => 'required|string|max:1000',
            'category' => 'required|in:general,observation,treatment,equipment',
        ]);

        $validated['created_by'] = Auth::id();

        $note = DoctorNote::create($validated);

        // Log activity
        AuditLog::log('create', "Menambahkan catatan dokter untuk device {$note->device_id} pada tanggal {$note->note_date}", 'DoctorNote', $note->id);

        return response()->json([
            'success' => true,
            'message' => 'Catatan dokter berhasil disimpan',
            'note' => $note->load('creator'),
        ]);
    }

    /**
     * Update doctor note
     */
    public function update(DoctorNote $note, Request $request)
    {
        if (!Auth::user() || Auth::id() !== $note->created_by) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'category' => 'required|in:general,observation,treatment,equipment',
        ]);

        $note->update($validated);

        // Log activity
        AuditLog::log('update', "Mengupdate catatan dokter ID {$note->id}", 'DoctorNote', $note->id);

        return response()->json([
            'success' => true,
            'message' => 'Catatan dokter berhasil diperbarui',
            'note' => $note,
        ]);
    }

    /**
     * Delete doctor note
     */
    public function destroy(DoctorNote $note)
    {
        if (!Auth::user() || Auth::id() !== $note->created_by) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $noteId = $note->id;
        $note->delete();

        // Log activity
        AuditLog::log('delete', "Menghapus catatan dokter ID {$noteId}", 'DoctorNote', $noteId);

        return response()->json([
            'success' => true,
            'message' => 'Catatan dokter berhasil dihapus',
        ]);
    }

    /**
     * Get notes for a date range
     */
    public function getRange(Device $device, Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $notes = DoctorNote::where('device_id', $device->id)
            ->whereDate('note_date', '>=', $startDate)
            ->whereDate('note_date', '<=', $endDate)
            ->with('creator')
            ->orderBy('note_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('note_date');

        return response()->json([
            'success' => true,
            'notes' => $notes,
        ]);
    }
}
