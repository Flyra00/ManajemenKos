<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $roles = explode('|', $role);

        // Jika user memiliki role tenant dan mencoba mengakses area admin/owner
        if ($user->hasRole('tenant') && ! in_array('tenant', $roles, true)) {
            abort(403, 'Akses ditolak: Anda login sebagai anak kos (tenant) dan tidak memiliki izin mengakses area pengelola.');
        }

        // Jika user memiliki salah satu role yang diizinkan, izinkan akses
        if ($user->hasAnyRole($roles)) {
            return $next($request);
        }

        // Jika user adalah owner dan mencoba aksi administratif admin-only
        if ($user->hasRole('owner') && ! in_array('owner', $roles, true)) {
            abort(403, 'Akses dibatasi: Akun Owner hanya memiliki izin pantau / baca (Read-Only). Aksi ini hanya dapat dilakukan oleh Admin.');
        }

        // Jika user adalah staff dan mencoba aksi di luar izin staff (seperti kamar, fasilitas, penghuni, kontrak, setting)
        if ($user->hasRole('staff') && ! in_array('staff', $roles, true)) {
            abort(403, 'Akses dibatasi: Akun Staff hanya memiliki izin mengelola Maintenance, Pembayaran, Pengeluaran, dan Laporan.');
        }

        // User tanpa role sama sekali tidak memiliki izin apa pun atas area pengelola.
        if ($user->roles->isEmpty()) {
            abort(403, 'Akses ditolak: Akun Anda belum memiliki peran (role). Hubungi Admin untuk mengatur peran akun Anda.');
        }

        abort(403, 'Akses ditolak: Peran akun Anda tidak memiliki izin untuk halaman ini.');
    }
}

