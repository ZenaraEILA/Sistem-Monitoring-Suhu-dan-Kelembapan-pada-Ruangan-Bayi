<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string',
            'hospital_id' => 'nullable|string',
            'email' => 'nullable|string',
            'login_method' => 'required|in:password,code',
            'credential' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (!$request->filled('username') && !$request->filled('hospital_id') && !$request->filled('email')) {
            return back()->with('error', 'Silakan masukkan salah satu identitas: Username, NISN, atau Email.')->withInput();
        }

        $method = $request->login_method;
        $credential = $request->credential;

        // Find the user by whichever identifier was provided
        $query = User::query();
        if ($request->filled('username')) {
            $query->where('username', $request->username);
        } elseif ($request->filled('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        } elseif ($request->filled('email')) {
            $query->where('email', $request->email);
        }

        $user = $query->first();

        if (!$user) {
            return back()->with('error', 'Akun tidak ditemukan.')->withInput();
        }

        if (!$user->isActive()) {
            return back()->with('error', 'Akun Anda dinonaktifkan. Silakan hubungi Admin.')->withInput();
        }

        $authenticated = false;

        if ($method === 'password') {
            if (Hash::check($credential, $user->password)) {
                $authenticated = true;
            }
        } elseif ($method === 'code') {
            if ($user->security_code && $credential === $user->security_code) {
                $authenticated = true;
            }
        }

        if ($authenticated) {
            Auth::login($user);
            $request->session()->regenerate();

            // Record login log
            LoginLog::create([
                'user_id' => Auth::id(),
                'login_time' => now(),
                'ip_address' => $request->ip(),
            ]);

            return redirect()->route('dashboard')->with('success', 'Login berhasil!');
        }

        return back()->with('error', 'Password atau Code Keamanan salah.')->withInput();
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah logout.');
    }

}
