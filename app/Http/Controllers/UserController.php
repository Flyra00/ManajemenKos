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
}
