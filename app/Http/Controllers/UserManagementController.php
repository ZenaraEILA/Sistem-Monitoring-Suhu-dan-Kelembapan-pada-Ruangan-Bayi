<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserManagementController extends Controller
{
    /**
     * ✅ Display list of all users (Admin only)
     *
     * GET /admin/users
     * Menampilkan daftar semua user dengan pagination
     */
    public function index()
    {
        try {
            // Pagination: 15 users per halaman
            $users = User::paginate(15);

            return view('admin.users.index', [
                'users' => $users,
                'totalUsers' => User::count(),
                'totalAdmins' => User::where('role', 'admin')->count(),
                'totalPetugas' => User::where('role', 'petugas')->count(),
                'activeUsers' => User::where('is_active', true)->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing users list', [
                'error' => $e->getMessage(),
                'admin_id' => Auth::id(),
            ]);

            return redirect()->route('dashboard')
                ->with('error', 'Terjadi kesalahan saat memuat data user');
        }
    }

    /**
     * ✅ Show user details (Admin only)
     *
     * GET /admin/users/{id}
     */
    public function show(User $user)
    {
        return view('admin.users.show', [
            'user' => $user,
            'canChangeRole' => Auth::user()->canChangeRoleOf($user),
        ]);
    }

    /**
     * 🔐 Update user role (Admin only)
     *
     * POST /admin/users/{id}/update-role
     * 
     * Body requirement:
     * {
     *   "role": "admin" atau "petugas"
     * }
     * 
     * Aturan:
     * - Admin tidak bisa mengubah role dirinya sendiri
     * - Hanya admin yang bisa mengubah role
     * - Role hanya bisa admin atau petugas
     */
    public function updateRole(Request $request, User $user)
    {
        // 🔐 Validasi input
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,petugas'],
        ], [
            'role.required' => 'Role harus diisi',
            'role.in' => 'Role hanya boleh admin atau petugas',
        ]);

        $currentUser = Auth::user();

        // 🔐 Cek: Admin tidak bisa mengubah role dirinya sendiri
        if ($user->id === $currentUser->id) {
            Log::warning('Admin coba ubah role dirinya sendiri', [
                'admin_id' => $currentUser->id,
                'attempted_new_role' => $validated['role'],
            ]);

            return redirect()->back()
                ->with('error', 'Anda tidak dapat mengubah role diri sendiri');
        }

        // 🔐 Cek: Hanya admin yang bisa update role
        if (!$currentUser->canChangeRoleOf($user)) {
            Log::warning('Unauthorized role change attempt', [
                'user_id' => $currentUser->id,
                'target_user_id' => $user->id,
            ]);

            return redirect()->back()
                ->with('error', 'Anda tidak memiliki izin untuk mengubah role user');
        }

        try {
            $oldRole = $user->role;
            $newRole = $validated['role'];

            // ✅ Update role menggunakan method (dengan validasi)
            $user->updateRole($newRole);

            // 📝 Log activity untuk audit trail
            Log::info('User role berhasil diubah', [
                'changed_by_id' => $currentUser->id,
                'changed_by_name' => $currentUser->name,
                'changed_by_email' => $currentUser->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'old_role' => $oldRole,
                'new_role' => $newRole,
                'timestamp' => now(),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('admin.users.show', $user)
                ->with('success', "✅ Role user {$user->name} berhasil diubah dari {$oldRole} menjadi {$newRole}");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', 'Error: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error updating user role', [
                'error' => $e->getMessage(),
                'admin_id' => $currentUser->id,
                'user_id' => $user->id,
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengubah role');
        }
    }

    /**
     * 🔐 Deactivate user (Admin only)
     *
     * POST /admin/users/{id}/deactivate
     * Menonaktifkan akun user tanpa menghapusnya
     */
    public function deactivateUser(Request $request, User $user)
    {
        $currentUser = Auth::user();

        // 🔐 Cek: Admin tidak bisa deactivate dirinya sendiri
        if ($user->id === $currentUser->id) {
            Log::warning('Admin coba deactivate akun dirinya sendiri', [
                'admin_id' => $currentUser->id,
            ]);

            return redirect()->back()
                ->with('error', 'Anda tidak dapat menonaktifkan akun diri sendiri');
        }

        // 🔐 Cek: Hanya admin
        if (!$currentUser->canDeactivateUser($user)) {
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki izin');
        }

        try {
            $user->deactivate();

            Log::warning('User account deactivated', [
                'deactivated_by_id' => $currentUser->id,
                'deactivated_by_email' => $currentUser->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'timestamp' => now(),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "✅ User {$user->email} berhasil dinonaktifkan");
        } catch (\Exception $e) {
            Log::error('Error deactivating user', [
                'error' => $e->getMessage(),
                'admin_id' => $currentUser->id,
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menonaktifkan user');
        }
    }

    /**
     * 🔐 Reactivate user (Admin only)
     *
     * POST /admin/users/{id}/activate
     * Mengaktifkan kembali akun user yang sudah dinonaktifkan
     */
    public function activateUser(Request $request, User $user)
    {
        $currentUser = Auth::user();

        // 🔐 Hanya admin
        if (!$currentUser->isAdmin()) {
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki izin');
        }

        try {
            $user->activate();

            Log::info('User account activated', [
                'activated_by_id' => $currentUser->id,
                'activated_by_email' => $currentUser->email,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'timestamp' => now(),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('admin.users.index')
                ->with('success', "✅ User {$user->email} berhasil diaktifkan kembali");
        } catch (\Exception $e) {
            Log::error('Error activating user', [
                'error' => $e->getMessage(),
                'admin_id' => $currentUser->id,
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengaktifkan user');
        }
    }
}
