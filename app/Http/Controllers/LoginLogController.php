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

        return view('monitoring.login-logs', compact('loginLogs', 'startDate', 'endDate'));
    }
}
