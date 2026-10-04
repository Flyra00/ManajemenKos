<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\KosSetting;
use App\Models\Room;
use App\Services\BillingService;
use Illuminate\Http\Request;

class PublicRoomController extends Controller
{
    /**
     * Tampilkan landing page utama untuk promosi dan penyewaan kamar kos.
     */
    public function landing(BillingService $billingService)
    {
        // Hanya ambil kamar yang aktif dan berstatus 'available' (kosong)
        $availableRooms = Room::with('facilities')
            ->where('is_active', true)
            ->where('status', 'available')
            ->orderBy('floor')
            ->orderBy('room_number')
            ->take(6)
            ->get();

        $totalAvailable = Room::where('is_active', true)
            ->where('status', 'available')
            ->count();

        $totalRooms = Room::where('is_active', true)->count();

        $startingPrice = Room::where('is_active', true)
            ->where('status', 'available')
            ->min('price') ?: 1000000;

        $facilities = Facility::latest()->take(8)->get();

        $featuredRoom = $availableRooms->first();

        $kosSettings = $billingService->getKosSettings();

        // Format nomor WhatsApp untuk chat langsung
        $cleanPhone = $billingService->formatPhoneNumber($kosSettings['phone'] ?? '081234567890');
        $waMessage = urlencode("Halo Pengelola {$kosSettings['name']}, saya tertarik untuk menyewa kamar kos. Apakah saat ini masih ada kamar yang tersedia?");
        $waUrl = "https://wa.me/{$cleanPhone}?text={$waMessage}";

        return view('welcome', compact(
            'availableRooms',
            'featuredRoom',
            'totalAvailable',
            'totalRooms',
            'startingPrice',
            'facilities',
            'kosSettings',
            'waUrl'
        ));
    }

    /**
     * Tampilkan katalog publik kamar yang tersedia untuk disewa.
     */
    public function index(Request $request)
    {
        $query = Room::with('facilities')
            ->where('is_active', true)
            ->where('status', 'available');

        if ($request->filled('q')) {
            $query->where('room_number', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('floor') && $request->floor !== 'semua') {
            $query->where('floor', $request->floor);
        }

        if ($request->filled('max_price') && is_numeric($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }

        $rooms = $query->orderBy('floor')->orderBy('room_number')->paginate(9)->withQueryString();
        $floors = Room::where('is_active', true)->distinct()->pluck('floor')->sort()->values();

        return view('public.rooms.index', compact('rooms', 'floors'));
    }

    /**
     * Tampilkan detail spesifikasi kamar beserta formulir pengajuan sewa.
     */
    public function show(Room $room)
    {
        if (! $room->is_active) {
            abort(404, 'Kamar tidak tersedia atau telah dinonaktifkan.');
        }

        $room->load('facilities');

        // Membaca preferensi kos tersimpan
        $kosSettings = KosSetting::settings();

        return view('public.rooms.show', compact('room', 'kosSettings'));
    }
}
