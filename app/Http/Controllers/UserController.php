<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh pengguna dan manajemen peran.
     */
    public function index(Request $request)
    {
        $query = User::with(['roles', 'tenant']);

        // Filter pencarian nama / email / nomor telepon
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        // Filter berdasarkan peran (Role)
        if ($request->filled('role')) {
            $roleName = $request->role;
            $query->whereHas('roles', function ($r) use ($roleName) {
                $r->where('name', $roleName);
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all();

        // Ringkasan Statistik Pengguna
        $totalUsers = User::count();
        $totalAdmins = User::role('admin')->count();
        $totalOwners = User::role('owner')->count();
        $totalStaff = User::role('staff')->count();
        $totalTenants = User::role('tenant')->count();

        return view('users.index', compact(
            'users',
            'roles',
            'totalUsers',
            'totalAdmins',
            'totalOwners',
            'totalStaff',
            'totalTenants'
        ));
    }

    /**
     * Perbarui peran pengguna (Spatie Laravel Permission).
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,owner,staff,tenant'],
        ]);

        $user->syncRoles([$validated['role']]);

        return back()->with('success', "Peran pengguna {$user->name} berhasil diperbarui menjadi " . strtoupper($validated['role']) . ".");
    }

    /**
     * Reset kata sandi pengguna ke default (password123) oleh Admin.
     */
    public function resetPassword(User $user)
    {
        $defaultPassword = 'password123';
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($defaultPassword),
        ]);

        return back()->with('success', "Kata sandi untuk {$user->name} ({$user->email}) berhasil direset menjadi: {$defaultPassword}");
    }

    /**
     * Verifikasi email pengguna secara manual oleh Admin.
     */
    public function verifyEmail(User $user)
    {
        if (is_null($user->email_verified_at)) {
            $user->markEmailAsVerified();
        }

        return back()->with('success', "Akun {$user->name} ({$user->email}) berhasil diverifikasi manual oleh Admin.");
    }
}
