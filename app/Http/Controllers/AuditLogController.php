<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display audit logs
     */
    public function index(Request $request)
    {
        $this->authorize('isAdmin');

        $query = AuditLog::with('user');

        // Filter untuk user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter untuk action
        if ($request->has('action')) {
            $query->where('action', $request->input('action'));
        }

        // Filter untuk date range
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('audit-logs.index', [
            'logs' => $logs,
            'actions' => AuditLog::ACTIONS,
        ]);
    }

    /**
     * Get audit logs for user
     */
    public function getUserLogs(Request $request)
    {
        $userId = $request->input('user_id', auth()->id());
        $days = $request->input('days', 30);

        $startDate = now()->subDays($days);

        $logs = AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }

    /**
     * Get activity summary
     */
    public function getActivitySummary(Request $request)
    {
        $days = $request->input('days', 7);
        $startDate = now()->subDays($days);

        $summary = [
            'total_logins' => AuditLog::where('action', 'login')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'total_data_changes' => AuditLog::whereIn('action', ['create', 'update', 'delete'])
                ->where('created_at', '>=', $startDate)
                ->count(),
            'total_exports' => AuditLog::where('action', 'export')
                ->where('created_at', '>=', $startDate)
                ->count(),
            'unique_users' => AuditLog::where('created_at', '>=', $startDate)
                ->distinct('user_id')
                ->count(),
        ];

        // Activity by user
        $byUser = AuditLog::where('created_at', '>=', $startDate)
            ->with('user')
            ->selectRaw('user_id, COUNT(*) as count')
            ->groupBy('user_id')
            ->orderByRaw('count DESC')
            ->get();

        // Activity by action
        $byAction = AuditLog::where('created_at', '>=', $startDate)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderByRaw('count DESC')
            ->get();

        return response()->json([
            'success' => true,
            'period_days' => $days,
            'summary' => $summary,
            'by_user' => $byUser,
            'by_action' => $byAction,
        ]);
    }

    /**
     * Export audit logs to CSV
     */
    public function export(Request $request)
    {
        $this->authorize('isAdmin');

        $query = AuditLog::with('user');

        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $filename = 'audit-logs-' . now()->format('Y-m-d-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Waktu', 'User', 'Aksi', 'Model', 'ID Model', 'Deskripsi', 'IP Address']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->user?->name ?? 'N/A',
                    $log->getActionLabel(),
                    $log->model_name,
                    $log->model_id,
                    $log->description,
                    $log->ip_address,
                ]);
            }

            fclose($file);
        };

        AuditLog::log('export', "Mengexport audit logs ({$logs->count()} records)", null, null);

        return response()->stream($callback, 200, $headers);
    }
}
