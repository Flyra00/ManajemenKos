<?php

namespace App\Services;

use App\Models\Lease;
use Illuminate\Support\Facades\DB;

/**
 * Siklus hidup kontrak sewa yang berjalan otomatis (tanpa aksi admin).
 */
class LeaseService
{
    /**
     * Tutup otomatis kontrak yang masa aktifnya sudah lewat.
     *
     * Kontrak berstatus 'active' yang end_date-nya sudah berlalu ditandai
     * 'completed', dan kamarnya dibebaskan kembali menjadi 'available' bila
     * tidak ada kontrak aktif lain di kamar tersebut. Kamar yang sedang
     * 'maintenance' sengaja tidak diubah.
     *
     * @return int jumlah kontrak yang ditutup
     */
    public function expireOverdueLeases(): int
    {
        $expiredLeases = Lease::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', now()->toDateString())
            ->get();

        $closed = 0;

        foreach ($expiredLeases as $lease) {
            DB::transaction(function () use ($lease) {
                $lease->update(['status' => 'completed']);

                if (! $lease->room) {
                    return;
                }

                $stillHasActiveLease = Lease::where('room_id', $lease->room_id)
                    ->where('status', 'active')
                    ->whereKeyNot($lease->id)
                    ->exists();

                if (! $stillHasActiveLease && $lease->room->status === 'occupied') {
                    $lease->room->update(['status' => 'available']);
                }
            });

            $closed++;
        }

        return $closed;
    }
}
