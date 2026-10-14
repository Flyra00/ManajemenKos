<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
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

        $newRole = $validated['role'];
        $actor = $request->user();

        // Cegah admin mengubah peran akunnya sendiri (anti lockout / self-escalation).
        if ($actor && $user->is($actor)) {
            return back()->with('error', 'Anda tidak dapat mengubah peran akun Anda sendiri. Minta admin lain untuk melakukannya.');
        }

        // Lindungi akun Owner: peran tertinggi tidak boleh diubah dari panel admin.
        if ($user->hasRole('owner') && $newRole !== 'owner') {
            return back()->with('error', 'Peran akun Owner tidak dapat diubah dari panel admin.');
        }

        $user->syncRoles([$newRole]);

        return back()->with('success', "Peran pengguna {$user->name} berhasil diperbarui menjadi " . strtoupper($newRole) . ".");
    }

    /**
     * Kirim tautan atur-ulang kata sandi ke email pemilik akun.
     *
     * Sebelumnya endpoint ini menimpa kata sandi dengan nilai default yang bisa
     * ditebak ('password123') lalu menampilkannya sebagai plaintext di flash
     * message. Kedua hal itu kini dihapus: admin hanya memicu pengiriman tautan,
     * dan hanya pemilik email yang dapat menetapkan kata sandi barunya sendiri.
     */
    public function resetPassword(User $user)
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->with('error', "Gagal mengirim tautan atur ulang kata sandi ke email {$user->email}. Pastikan alamat email valid dan coba lagi.");
        }

        return back()->with('success', "Tautan atur ulang kata sandi telah dikirim ke email {$user->email}. Penghuni menetapkan sendiri kata sandi barunya.");
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
