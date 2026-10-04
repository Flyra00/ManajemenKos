<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class MaintenanceController extends Controller
{
    /**
     * Cek apakah user adalah tenant
     */
    protected function isTenantUser(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasRole('tenant') || ($user->tenant()->exists() && !$user->hasRole('admin') && !$user->hasRole('owner') && !$user->hasRole('staff'));
    }

    /**
     * Dapatkan daftar user yang berhak menjadi staf/teknisi penanggung jawab (bukan penyewa/tenant).
     */
    protected function getStaffUsers()
    {
        return User::whereDoesntHave('roles', function ($q) {
            $q->where('name', 'tenant');
        })->whereDoesntHave('tenant')->get();
    }

    /**
     * Sinkronisasikan biaya maintenance ke modul Expenses secara otomatis.
     */
    protected function syncMaintenanceExpense(MaintenanceRequest $maintenance): void
    {
        if ($maintenance->cost > 0) {
            $maintenance->loadMissing('room');
            $userId = auth()->id() ?? $maintenance->handled_by ?? User::first()?->id;
            $expenseDate = $maintenance->resolved_at
                ? Carbon::parse($maintenance->resolved_at)->toDateString()
                : ($maintenance->reported_at ? Carbon::parse($maintenance->reported_at)->toDateString() : now()->toDateString());

            Expense::updateOrCreate(
                ['description' => "Otomatis dari Tiket Maintenance #{$maintenance->id}"],
                [
                    'title'        => "Biaya Perbaikan: {$maintenance->title} (Kamar " . ($maintenance->room->room_number ?? '—') . ")",
                    'amount'       => $maintenance->cost,
                    'expense_date' => $expenseDate,
                    'user_id'      => $userId,
                ]
            );
        } else {
            Expense::where('description', "Otomatis dari Tiket Maintenance #{$maintenance->id}")->delete();
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isTenant = $this->isTenantUser($user);

        $query = MaintenanceRequest::with(['room', 'tenant.user', 'handler'])->latest();

        if ($isTenant) {
            $tenantId = $user->tenant?->id ?? 0;
            $query->where('tenant_id', $tenantId);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('room', function ($rq) use ($search) {
                      $rq->where('room_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('tenant.user', function ($tq) use ($search) {
                      $tq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $maintenances = $query->paginate(10)->withQueryString();

        if ($isTenant) {
            $tenantId = $user->tenant?->id ?? 0;
            $stats = [
                'total'       => MaintenanceRequest::where('tenant_id', $tenantId)->count(),
                'reported'    => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'reported')->count(),
                'in_progress' => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'in_progress')->count(),
                'completed'   => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'completed')->count(),
                'cancelled'   => MaintenanceRequest::where('tenant_id', $tenantId)->where('status', 'cancelled')->count(),
                'total_cost'  => 0,
            ];
        } else {
            $stats = [
                'total'       => MaintenanceRequest::count(),
                'reported'    => MaintenanceRequest::where('status', 'reported')->count(),
                'in_progress' => MaintenanceRequest::where('status', 'in_progress')->count(),
                'completed'   => MaintenanceRequest::where('status', 'completed')->count(),
                'cancelled'   => MaintenanceRequest::where('status', 'cancelled')->count(),
                'total_cost'  => MaintenanceRequest::sum('cost'),
            ];
        }

        $users = $this->getStaffUsers();

        return view('maintenance.index', compact('maintenances', 'stats', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $isTenant = $this->isTenantUser($user);

        $selectedRoomId = $request->input('room_id');
        $selectedTenantId = $request->input('tenant_id');

        if ($isTenant) {
            $tenant = $user->tenant;
            if (!$tenant) {
                return redirect()->route('dashboard')
                    ->with('error', 'Anda belum memiliki kamar sewa aktif. Silakan pilih dan sewa kamar terlebih dahulu untuk mengajukan perbaikan.');
            }
            $activeRoomIds = $tenant->leases()->whereIn('status', ['active', 'pending'])->pluck('room_id');
            if ($activeRoomIds->isEmpty()) {
                return redirect()->route('dashboard')
                    ->with('error', 'Anda belum memiliki kamar sewa aktif. Silakan pilih dan sewa kamar terlebih dahulu untuk mengajukan perbaikan.');
            }
            $rooms = Room::whereIn('id', $activeRoomIds)->get();
            $tenants = collect([$tenant->load('user')]);
            $users = collect();
            $selectedTenantId = $tenant->id;
            $selectedRoomId = $selectedRoomId ?? $rooms->first()?->id;
        } else {
            $rooms = Room::all();
            $tenants = Tenant::with('user')->get();
            $users = $this->getStaffUsers();
        }

        return view('maintenance.create', compact('rooms', 'tenants', 'users', 'selectedRoomId', 'selectedTenantId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $isTenant = $this->isTenantUser($user);

        if ($isTenant) {
            $tenant = $user->tenant;
            if (!$tenant) {
                return redirect()->route('dashboard')
                    ->with('error', 'Anda belum memiliki kamar sewa aktif. Silakan pilih dan sewa kamar terlebih dahulu.');
            }

            $allowedRoomIds = $tenant->leases()->whereIn('status', ['active', 'pending'])->pluck('room_id');
            if ($allowedRoomIds->isEmpty()) {
                return redirect()->route('dashboard')
                    ->with('error', 'Anda belum memiliki kamar sewa aktif. Silakan sewa kamar terlebih dahulu untuk mengajukan keluhan.');
            }

            $validated = $request->validate([
                'title'       => ['required', 'string', 'max:255'],
                'room_id'     => ['required', 'exists:rooms,id'],
                'priority'    => ['required', 'in:low,medium,high'],
                'description' => ['required', 'string'],
                'image_path'  => ['nullable', 'image', 'max:2048'],
            ]);

            // Jika penyewa memiliki kontrak sewa kamar, validasi agar tidak melaporkan kamar yang salah
            if (!$allowedRoomIds->contains($validated['room_id'])) {
                return back()->withInput()->withErrors(['room_id' => 'Anda hanya dapat melaporkan keluhan untuk kamar yang Anda sewa.']);
            }

            $imagePath = null;
            if ($request->hasFile('image_path')) {
                $imagePath = $request->file('image_path')->store('maintenance', 'public');
            }

            MaintenanceRequest::create([
                'title'       => $validated['title'],
                'room_id'     => $validated['room_id'],
                'tenant_id'   => $tenant->id,
                'priority'    => $validated['priority'],
                'status'      => 'reported',
                'cost'        => 0,
                'handled_by'  => null,
                'description' => $validated['description'],
                'image_path'  => $imagePath,
                'reported_at' => now(),
                'resolved_at' => null,
            ]);

            return redirect()
                ->route('maintenance.index')
                ->with('success', 'Laporan keluhan Anda berhasil dikirim dan akan segera diproses.');
        }

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'room_id'     => ['required', 'exists:rooms,id'],
            'tenant_id'   => ['required', 'exists:tenants,id'],
            'priority'    => ['required', 'in:low,medium,high'],
            'status'      => ['required', 'in:reported,in_progress,completed,cancelled'],
            'cost'        => ['nullable', 'numeric', 'min:0'],
            'handled_by'  => ['nullable', 'exists:users,id'],
            'description' => ['required', 'string'],
            'image_path'  => ['nullable', 'image', 'max:2048'],
            'reported_at' => ['nullable', 'date'],
            'resolved_at' => ['nullable', 'date'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image_path')) {
            $imagePath = $request->file('image_path')->store('maintenance', 'public');
        }

        $reportedAt = $validated['reported_at'] ?? now();
        $resolvedAt = $validated['resolved_at'] ?? null;

        if ($validated['status'] === 'completed' && empty($resolvedAt)) {
            $resolvedAt = now();
        }

        $maintenance = MaintenanceRequest::create([
            'title'       => $validated['title'],
            'room_id'     => $validated['room_id'],
            'tenant_id'   => $validated['tenant_id'],
            'priority'    => $validated['priority'],
            'status'      => $validated['status'],
            'cost'        => $validated['cost'] ?? 0,
            'handled_by'  => $validated['handled_by'] ?? null,
            'description' => $validated['description'],
            'image_path'  => $imagePath,
            'reported_at' => $reportedAt,
            'resolved_at' => $resolvedAt,
        ]);

        // SINKRONISASI BIAYA PERBAIKAN KE TABEL EXPENSES
        $this->syncMaintenanceExpense($maintenance);

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Laporan maintenance berhasil dibuat');
    }

    /**
     * Display the specified resource.
     */
    public function show(MaintenanceRequest $maintenance)
    {
        $user = auth()->user();
        $isTenant = $this->isTenantUser($user);

        if ($isTenant) {
            if ($maintenance->tenant_id !== $user->tenant?->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat tiket keluhan ini.');
            }
        }

        $maintenance->load(['room', 'tenant.user', 'handler']);
        return view('maintenance.show', compact('maintenance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MaintenanceRequest $maintenance)
    {
        $user = auth()->user();
        $isTenant = $this->isTenantUser($user);

        if ($isTenant) {
            if ($maintenance->tenant_id !== $user->tenant?->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk mengedit tiket keluhan ini.');
            }
            if ($maintenance->status !== 'reported') {
                return redirect()->route('maintenance.index')
                    ->with('error', 'Laporan yang sedang diproses atau telah selesai tidak dapat diedit kembali.');
            }
            $rooms = Room::where('id', $maintenance->room_id)->get();
            $tenants = collect([$user->tenant->load('user')]);
            $users = collect();
            return view('maintenance.edit', compact('maintenance', 'rooms', 'tenants', 'users', 'isTenant'));
        }

        $rooms = Room::all();
        $tenants = Tenant::with('user')->get();
        $users = $this->getStaffUsers();

        return view('maintenance.edit', compact('maintenance', 'rooms', 'tenants', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaintenanceRequest $maintenance)
    {
        $user = auth()->user();
        $isTenant = $this->isTenantUser($user);

        if ($isTenant) {
            if ($maintenance->tenant_id !== $user->tenant?->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk memperbarui tiket keluhan ini.');
            }
            if ($maintenance->status !== 'reported') {
                return redirect()->route('maintenance.index')
                    ->with('error', 'Laporan yang sedang diproses atau telah selesai tidak dapat diubah.');
            }

            $validated = $request->validate([
                'title'       => ['required', 'string', 'max:255'],
                'room_id'     => ['required', 'exists:rooms,id'],
                'priority'    => ['required', 'in:low,medium,high'],
                'description' => ['required', 'string'],
                'image_path'  => ['nullable', 'image', 'max:2048'],
            ]);

            $imagePath = $maintenance->image_path;
            if ($request->boolean('remove_image')) {
                if ($maintenance->image_path && Storage::disk('public')->exists($maintenance->image_path)) {
                    Storage::disk('public')->delete($maintenance->image_path);
                }
                $imagePath = null;
            } elseif ($request->hasFile('image_path')) {
                if ($maintenance->image_path && Storage::disk('public')->exists($maintenance->image_path)) {
                    Storage::disk('public')->delete($maintenance->image_path);
                }
                $imagePath = $request->file('image_path')->store('maintenance', 'public');
            }

            $maintenance->update([
                'title'       => $validated['title'],
                'room_id'     => $validated['room_id'],
                'priority'    => $validated['priority'],
                'description' => $validated['description'],
                'image_path'  => $imagePath,
            ]);

            return redirect()
                ->route('maintenance.index')
                ->with('success', 'Laporan keluhan berhasil diperbarui');
        }

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'room_id'     => ['required', 'exists:rooms,id'],
            'tenant_id'   => ['required', 'exists:tenants,id'],
            'priority'    => ['required', 'in:low,medium,high'],
            'status'      => ['required', 'in:reported,in_progress,completed,cancelled'],
            'cost'        => ['nullable', 'numeric', 'min:0'],
            'handled_by'  => ['nullable', 'exists:users,id'],
            'description' => ['required', 'string'],
            'image_path'  => ['nullable', 'image', 'max:2048'],
            'reported_at' => ['nullable', 'date'],
            'resolved_at' => ['nullable', 'date'],
        ]);

        $imagePath = $maintenance->image_path;

        if ($request->boolean('remove_image')) {
            if ($maintenance->image_path && Storage::disk('public')->exists($maintenance->image_path)) {
                Storage::disk('public')->delete($maintenance->image_path);
            }
            $imagePath = null;
        } elseif ($request->hasFile('image_path')) {
            if ($maintenance->image_path && Storage::disk('public')->exists($maintenance->image_path)) {
                Storage::disk('public')->delete($maintenance->image_path);
            }
            $imagePath = $request->file('image_path')->store('maintenance', 'public');
        }

        $resolvedAt = $validated['resolved_at'] ?? $maintenance->resolved_at;
        if ($validated['status'] === 'completed' && empty($resolvedAt)) {
            $resolvedAt = now();
        }

        $maintenance->update([
            'title'       => $validated['title'],
            'room_id'     => $validated['room_id'],
            'tenant_id'   => $validated['tenant_id'],
            'priority'    => $validated['priority'],
            'status'      => $validated['status'],
            'cost'        => $validated['cost'] ?? 0,
            'handled_by'  => $validated['handled_by'] ?? null,
            'description' => $validated['description'],
            'image_path'  => $imagePath,
            'reported_at' => $validated['reported_at'] ?? $maintenance->reported_at,
            'resolved_at' => $resolvedAt,
        ]);

        // SINKRONISASI BIAYA PERBAIKAN KE TABEL EXPENSES
        $this->syncMaintenanceExpense($maintenance);

        // SINKRONISASI STATUS KAMAR SAAT PERBAIKAN SELESAI
        if ($maintenance->status === 'completed' && $maintenance->room && $maintenance->room->status === 'maintenance') {
            $hasOtherPendingMaintenance = MaintenanceRequest::where('room_id', $maintenance->room_id)
                ->where('id', '!=', $maintenance->id)
                ->whereIn('status', ['reported', 'in_progress'])
                ->exists();

            if (!$hasOtherPendingMaintenance) {
                $newStatus = $maintenance->room->activeLease()->exists() ? 'occupied' : 'available';
                $maintenance->room->update(['status' => $newStatus]);
            }
        }


        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Laporan maintenance berhasil diperbarui');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaintenanceRequest $maintenance)
    {
        $user = auth()->user();
        $isTenant = $this->isTenantUser($user);

        if ($isTenant) {
            if ($maintenance->tenant_id !== $user->tenant?->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk membatalkan tiket keluhan ini.');
            }
            if ($maintenance->status !== 'reported') {
                return redirect()->route('maintenance.index')
                    ->with('error', 'Laporan yang sedang diproses atau telah selesai tidak dapat dibatalkan.');
            }
        }

        if ($maintenance->image_path && Storage::disk('public')->exists($maintenance->image_path)) {
            Storage::disk('public')->delete($maintenance->image_path);
        }

        if ($maintenance->completion_image && Storage::disk('public')->exists($maintenance->completion_image)) {
            Storage::disk('public')->delete($maintenance->completion_image);
        }

        Expense::where('description', "Otomatis dari Tiket Maintenance #{$maintenance->id}")->delete();

        $maintenance->delete();

        return redirect()
            ->route('maintenance.index')
            ->with('success', 'Laporan maintenance berhasil dihapus');

    }

    /**
     * Update status pengerjaan tiket maintenance secara bertahap / cepat.
     */
    public function updateStatus(Request $request, MaintenanceRequest $maintenance)
    {
        $rules = [
            'status'           => ['required', 'in:reported,in_progress,completed,cancelled'],
            'handled_by'       => ['nullable', 'exists:users,id'],
            'cost'             => ['nullable', 'numeric', 'min:0'],
            'completion_image' => ['nullable', 'image', 'max:2048'],
        ];

        // Jika mengubah status menjadi diproses (in_progress), wajib memilih staff/teknisi penanggung jawab
        if ($request->input('status') === 'in_progress') {
            $rules['handled_by'] = ['required', 'exists:users,id'];
        }

        $validated = $request->validate($rules, [
            'handled_by.required'    => 'Harap pilih staff atau teknisi yang akan menangani perbaikan ini.',
            'completion_image.image' => 'File bukti penyelesaian harus berupa gambar (JPG, PNG, JPEG).',
            'completion_image.max'   => 'Ukuran foto bukti penyelesaian maksimal 2MB.',
        ]);

        // Pastikan staff yang dipilih bukan seorang tenant (penyewa)
        if (!empty($validated['handled_by'])) {
            $staff = User::find($validated['handled_by']);
            if ($staff && $this->isTenantUser($staff)) {
                return back()->withInput()->withErrors([
                    'handled_by' => 'User yang dipilih adalah penyewa (tenant). Penghuni tidak dapat ditugaskan sebagai staff.',
                ]);
            }
        }

        $status = $validated['status'];
        $resolvedAt = $maintenance->resolved_at;
        if ($status === 'completed' && empty($resolvedAt)) {
            $resolvedAt = now();
        } elseif ($status !== 'completed' && $maintenance->status === 'completed') {
            $resolvedAt = null;
        }

        $completionImage = $maintenance->completion_image;
        if ($request->hasFile('completion_image')) {
            if ($maintenance->completion_image && Storage::disk('public')->exists($maintenance->completion_image)) {
                Storage::disk('public')->delete($maintenance->completion_image);
            }
            $completionImage = $request->file('completion_image')->store('maintenance', 'public');
        }

        $updateData = [
            'status'           => $status,
            'resolved_at'      => $resolvedAt,
            'completion_image' => $completionImage,
        ];

        if (array_key_exists('handled_by', $validated)) {
            $updateData['handled_by'] = !empty($validated['handled_by']) ? $validated['handled_by'] : null;
        }

        if (isset($validated['cost'])) {
            $updateData['cost'] = $validated['cost'];
        }

        $maintenance->update($updateData);

        // SINKRONISASI BIAYA PERBAIKAN KE TABEL EXPENSES
        $this->syncMaintenanceExpense($maintenance);

        // SINKRONISASI STATUS KAMAR SAAT PERBAIKAN SELESAI
        if ($maintenance->status === 'completed' && $maintenance->room && $maintenance->room->status === 'maintenance') {
            $hasOtherPendingMaintenance = MaintenanceRequest::where('room_id', $maintenance->room_id)
                ->where('id', '!=', $maintenance->id)
                ->whereIn('status', ['reported', 'in_progress'])
                ->exists();

            if (!$hasOtherPendingMaintenance) {
                $newStatus = $maintenance->room->activeLease()->exists() ? 'occupied' : 'available';
                $maintenance->room->update(['status' => $newStatus]);
            }
        }

        return redirect()->route('maintenance.index')
            ->with('success', "Status laporan '{$maintenance->title}' berhasil diperbarui.");
    }
}

