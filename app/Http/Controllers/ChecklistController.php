<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DailyChecklist;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChecklistController extends Controller
{
    /**
     * Show daily checklist for today
     */
    public function showToday(Device $device)
    {
        $today = Carbon::today();
        
        $checklist = DailyChecklist::where('device_id', $device->id)
            ->where('checklist_date', $today)
            ->first();

        if (!$checklist) {
            $checklist = DailyChecklist::create([
                'device_id' => $device->id,
                'petugas_id' => Auth::id(),
                'checklist_date' => $today,
                'items' => [
                    'room_condition' => false,
                    'ac_check' => false,
                    'ventilation_check' => false,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'checklist' => $checklist,
            'completion' => $checklist->getCompletionPercentage(),
        ]);
    }

    /**
     * Update checklist items
     */
    public function update(DailyChecklist $checklist, Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'notes' => 'nullable|string',
        ]);

        $checklist->update([
            'items' => $validated['items'],
            'notes' => $validated['notes'] ?? $checklist->notes,
            'completed_at' => now(),
        ]);

        // Log activity
        AuditLog::log('update', "Melengkapi daily checklist untuk device {$checklist->device_id}", 'DailyChecklist', $checklist->id);

        return response()->json([
            'success' => true,
            'message' => 'Checklist berhasil disimpan',
            'checklist' => $checklist,
            'completion' => $checklist->getCompletionPercentage(),
        ]);
    }

    /**
     * Get checklist history
     */
    public function history(Device $device, Request $request)
    {
        $days = $request->input('days', 30);
        $startDate = now()->subDays($days);

        $checklists = DailyChecklist::where('device_id', $device->id)
            ->where('checklist_date', '>=', $startDate)
            ->with('petugas')
            ->orderBy('checklist_date', 'desc')
            ->get();

        $completionStats = $checklists->map(function ($checklist) {
            return [
                'date' => $checklist->checklist_date,
                'completion' => $checklist->getCompletionPercentage(),
                'completed_at' => $checklist->completed_at,
                'petugas' => $checklist->petugas->name,
            ];
        });

        return response()->json([
            'success' => true,
            'checklists' => $checklists,
            'stats' => $completionStats,
        ]);
    }

    /**
     * Check if today checklist is completed
     */
    public function checkTodayStatus(Device $device)
    {
        $today = Carbon::today();
        
        $checklist = DailyChecklist::where('device_id', $device->id)
            ->where('checklist_date', $today)
            ->first();

        $isCompleted = $checklist && $checklist->isCompleted();
        $completion = $checklist?->getCompletionPercentage() ?? 0;

        return response()->json([
            'success' => true,
            'is_completed' => $isCompleted,
            'completion_percentage' => $completion,
            'checklist' => $checklist,
        ]);
    }
}
