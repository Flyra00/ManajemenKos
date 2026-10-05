<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Facility;
use App\Services\LeaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, LeaseService $leaseService)
    {
        // Tutup otomatis kontrak yang sudah berakhir agar status kamar akurat.
        $leaseService->expireOverdueLeases();

        $query = Room::with(['facilities', 'activeLease.tenant.user'])->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('room_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($floor = $request->input('floor')) {
            $query->where('floor', $floor);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->input('is_active'));
        }

        $rooms = $query->paginate(10)->withQueryString();

        $stats = [
            'total'       => Room::count(),
            'kosong'      => Room::where('status', 'available')->count(),
            'terisi'      => Room::where('status', 'occupied')->count(),
            'perbaikan'   => Room::where('status', 'maintenance')->count(),
            'total_floor' => Room::distinct('floor')->whereNotNull('floor')->count('floor') ?: 1,
        ];

        return view('rooms.index', compact('rooms', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $facilities = Facility::all();
        return view('rooms.create', compact('facilities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => [
                'required',
                'string',
                'max:20',
                'unique:rooms,room_number',
            ],
            'floor' => [
                'nullable',
                'string',
                'max:10',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'status' => [
                'required',
                'in:available,occupied,maintenance',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'image' => [
                'nullable',
                'image',
                'max:2048',
            ],
            'facilities' => [
                'nullable',
                'array',
            ],
            'facilities.*' => [
                'exists:facilities,id',
            ],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('rooms', 'public');
        }

        $room = Room::create([
            'room_number' => $validated['room_number'],
            'floor'       => $validated['floor'] ?? null,
            'price'       => $validated['price'],
            'status'      => $validated['status'],
            'is_active'   => $request->boolean('is_active', true),
            'description' => $validated['description'] ?? null,
            'image'       => $imagePath,
        ]);

        if (!empty($validated['facilities'])) {
            $room->facilities()->sync($validated['facilities']);
        }

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Kamar berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        $room->load(['facilities', 'leases.tenant.user', 'maintenanceRequests']);
        return view('rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        $facilities = Facility::all();
        $room->load('facilities');

        return view('rooms.edit', compact('room', 'facilities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => [
                'required',
                'string',
                'max:20',
                Rule::unique('rooms', 'room_number')->ignore($room->id),
            ],
            'floor' => [
                'nullable',
                'string',
                'max:10',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0',
            ],
            'status' => [
                'required',
                'in:available,occupied,maintenance',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'image' => [
                'nullable',
                'image',
                'max:2048',
            ],
            'facilities' => [
                'nullable',
                'array',
            ],
            'facilities.*' => [
                'exists:facilities,id',
            ],
        ]);

        $imagePath = $room->image;

        if ($request->hasFile('image')) {
            if ($room->image && Storage::disk('public')->exists($room->image)) {
                Storage::disk('public')->delete($room->image);
            }

            $imagePath = $request->file('image')->store('rooms', 'public');
        }

        $room->update([
            'room_number' => $validated['room_number'],
            'floor'       => $validated['floor'] ?? null,
            'price'       => $validated['price'],
            'status'      => $validated['status'],
            'is_active'   => $request->boolean('is_active', true),
            'description' => $validated['description'] ?? null,
            'image'       => $imagePath,
        ]);

        $room->facilities()->sync($request->input('facilities', []));

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Kamar berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        if ($room->activeLease()->exists()) {
            return redirect()
                ->route('rooms.index')
                ->with('error', 'Tidak dapat menghapus kamar yang masih memiliki kontrak sewa aktif.');
        }

        if ($room->maintenanceRequests()->whereIn('status', ['reported', 'in_progress'])->exists()) {
            return redirect()
                ->route('rooms.index')
                ->with('error', 'Tidak dapat menghapus kamar yang sedang dalam proses perbaikan/maintenance.');
        }

        // Lindungi riwayat keuangan: relasi leases -> payments memakai cascadeOnDelete,
        // jadi menghapus kamar yang pernah memiliki kontrak sewa (meski sudah selesai)
        // akan ikut menghapus seluruh riwayat tagihan & pembayarannya.
        // Nonaktifkan kamar (is_active = false) sebagai gantinya.
        if ($room->leases()->exists()) {
            return redirect()
                ->route('rooms.index')
                ->with('error', 'Tidak dapat menghapus kamar yang memiliki riwayat kontrak sewa. Nonaktifkan kamar ini sebagai gantinya agar riwayat pembayaran tetap tersimpan.');
        }

        if ($room->image && Storage::disk('public')->exists($room->image)) {
            Storage::disk('public')->delete($room->image);
        }

        $room->facilities()->detach();
        $room->delete();

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Kamar berhasil dihapus');
    }
}

