<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class TenantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tenant::with(['user', 'activeLease.room'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ktp_number', 'like', "%{$search}%")
                  ->orWhere('job', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'Aktif') {
                $query->whereHas('leases', fn ($l) => $l->where('status', 'active'));
            } elseif ($status === 'Keluar') {
                $query->whereDoesntHave('leases', fn ($l) => $l->where('status', 'active'));
            }
        }

        $tenants = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => Tenant::count(),
            'aktif' => Tenant::whereHas('leases', fn ($l) => $l->where('status', 'active'))->count(),
            'baru'  => Tenant::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'keluar'=> Tenant::whereDoesntHave('leases', fn ($l) => $l->where('status', 'active'))->has('leases')->count(),
        ];

        return view('tenants.index', compact('tenants', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tenants.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'ktp_number' => ['required', 'string', 'max:20', 'unique:tenants,ktp_number'],
            'job' => ['nullable', 'string', 'max:100'],
            'emergency_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact' => ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'password' => Hash::make($validated['password']),
            ]);
            $user->assignRole('tenant');

            Tenant::create([
                'user_id' => $user->id,
                'ktp_number' => $validated['ktp_number'],
                'job' => $validated['job'] ?? null,
                'emergency_name' => $validated['emergency_name'] ?? null,
                'emergency_contact' => $validated['emergency_contact'] ?? null,
            ]);
        });

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Penghuni berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tenant $tenant)
    {
        $tenant->load(['user', 'leases.room', 'maintenanceRequests']);
        return view('tenants.show', compact('tenant'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tenant $tenant)
    {
        return redirect()
            ->route('tenants.show', $tenant)
            ->with('info', 'Demi menjaga integritas dan privasi akun, profil penghuni dikelola mandiri oleh penghuni yang bersangkutan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant)
    {
        return redirect()
            ->route('tenants.show', $tenant)
            ->with('error', 'Admin tidak memiliki izin untuk mengubah data akun penghuni secara sepihak.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tenant $tenant)
    {
        return redirect()
            ->route('tenants.index')
            ->with('error', 'Akun penghuni tidak dapat dihapus oleh Admin. Masa aktif akun dikendalikan oleh durasi hari sewa dan proses check-out resmi.');
    }
}

