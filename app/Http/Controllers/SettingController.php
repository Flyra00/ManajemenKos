<?php

namespace App\Http\Controllers;

use App\Models\KosSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class SettingController extends Controller
{
    /**
     * Tampilkan halaman pengaturan sistem.
     */
    public function index()
    {
        $user = auth()->user();
        $kosSettings = KosSetting::settings();
        $users = User::with('roles')->paginate(10);
        $roles = Role::all();

        return view('settings.index', compact('user', 'kosSettings', 'users', 'roles'));
    }

    /**
     * Perbarui data profil akun admin yang sedang login.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil akun berhasil diperbarui.');
    }

    /**
     * Perbarui kata sandi akun login.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }

    /**
     * Perbarui informasi operasional dan rekening pembayaran kos.
     */
    public function updateKosInfo(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'address'      => ['nullable', 'string', 'max:500'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'email'        => ['nullable', 'email', 'max:100'],
            'latitude'     => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'    => ['nullable', 'numeric', 'between:-180,180'],
            'map_zoom'     => ['nullable', 'integer', 'between:1,20'],
            'billing_due'  => ['nullable', 'integer', 'min:1', 'max:31'],
        ]);

        KosSetting::current()->update($validated);

        return back()->with('success', 'Informasi dan lokasi kos berhasil diperbarui.');
    }

    /**
     * Ubah peran pengguna (Spatie Role).
     */
    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,owner,staff,tenant'],
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', "Peran pengguna {$user->name} berhasil diubah menjadi {$validated['role']}.");
    }

}
