<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class ProfileController extends Controller
{
    /**
     * Show user profile
     */
    public function show()
    {
        $user = Auth::user();

        return view('profile.show', compact('user'));
    }

    /**
     * Show edit profile form
     */
    public function edit()
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Update user profile information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email:rfc,dns|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->route('profile.show')
            ->with('success', 'Profil berhasil diperbarui');
    }

    /**
     * Show change password form
     */
    public function editPassword()
    {
        return view('profile.edit-password');
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak sesuai',
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.show')
            ->with('success', 'Password berhasil diubah');
    }

    /**
     * Upload profile photo
     */
    public function uploadPhoto(Request $request)
    {
        $validated = $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp,bmp,svg,tiff|max:5120',
        ], [
            'profile_photo.required' => 'Silakan pilih file foto',
            'profile_photo.image' => 'File harus berupa gambar',
            'profile_photo.mimes' => 'Format gambar harus JPEG, PNG, JPG, GIF, WebP, BMP, SVG, atau TIFF',
            'profile_photo.max' => 'Ukuran foto maksimal 5 MB',
        ]);

        $user = Auth::user();

        // Delete old photo if exists
        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Store new photo
        $file = $request->file('profile_photo');
        $filename = 'profile-' . $user->id . '-' . Str::random(10) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('profile-photos', $filename, 'public');

        // Update user with photo path
        $user->update(['profile_photo_path' => $path]);

        return redirect()->route('profile.show')
            ->with('success', 'Foto profil berhasil diupload');
    }

    /**
     * Delete profile photo
     */
    public function deletePhoto()
    {
        $user = Auth::user();

        if ($user->profile_photo_path) {
            // Delete file from storage
            if (Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Update user to remove photo path
            $user->update(['profile_photo_path' => null]);

            return redirect()->route('profile.show')
                ->with('success', 'Foto profil berhasil dihapus');
        }

        return redirect()->route('profile.show')
            ->with('warning', 'Tidak ada foto profil untuk dihapus');
    }
}
