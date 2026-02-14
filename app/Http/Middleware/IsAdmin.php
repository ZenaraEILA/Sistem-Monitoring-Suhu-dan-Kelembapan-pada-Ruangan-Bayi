<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * 🔐 Handle an incoming request.
     * 
     * Middleware ini memastikan:
     * 1. User sudah login (authenticated)
     * 2. User memiliki role 'admin'
     * 3. User status aktif
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ Check if user authenticated
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu');
        }

        $user = Auth::user();

        // ✅ Check if user is active
        if (!$user->isActive()) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Hubungi administrator.');
        }

        // ✅ Check if user is admin
        if (!$user->isAdmin()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini. Hanya admin yang diizinkan.');
        }

        // 📝 Log admin activity (for security auditing)
        Log::info('Admin mengakses', [
            'admin_id' => $user->id,
            'admin_name' => $user->name,
            'admin_email' => $user->email,
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        return $next($request);
    }
}
