<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LoginLogController extends Controller
{
    /**
     * Show login history
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = LoginLog::with('user')->latest('login_time');

        if ($startDate) {
            $query->where('login_time', '>=', Carbon::parse($startDate)->startOfDay());
        }

        if ($endDate) {
            $query->where('login_time', '<=', Carbon::parse($endDate)->endOfDay());
        }

        $loginLogs = $query->paginate(50);

        // Calculate statistics
        $totalLogin = $loginLogs->total();
        $adminCount = LoginLog::with('user')
            ->whereHas('user', function ($q) {
                $q->where('role', 'admin');
            });
        
        if ($startDate) {
            $adminCount->where('login_time', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate) {
            $adminCount->where('login_time', '<=', Carbon::parse($endDate)->endOfDay());
        }
        $adminCount = $adminCount->count();

        $petugasCount = LoginLog::with('user')
            ->whereHas('user', function ($q) {
                $q->where('role', 'petugas');
            });
        
        if ($startDate) {
            $petugasCount->where('login_time', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate) {
            $petugasCount->where('login_time', '<=', Carbon::parse($endDate)->endOfDay());
        }
        $petugasCount = $petugasCount->count();

        $uniqueUsers = LoginLog::with('user')
            ->distinct('user_id');
        
        if ($startDate) {
            $uniqueUsers->where('login_time', '>=', Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate) {
            $uniqueUsers->where('login_time', '<=', Carbon::parse($endDate)->endOfDay());
        }
        $uniqueUsers = $uniqueUsers->pluck('user_id')->count();

        return view('monitoring.login-logs', compact('loginLogs', 'startDate', 'endDate', 'totalLogin', 'adminCount', 'petugasCount', 'uniqueUsers'));
    }
}
