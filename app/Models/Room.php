<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    //
    protected $fillable = [
        "room_number",
        "floor",
        "price",
        "status",
        "is_active",
        "image",
        "description",
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function facilities()
    {
        return $this->belongsToMany(
            Facility::class,
            'room_facilities'
        );
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease()
    {
        return $this->hasOne(Lease::class)->where('status', 'active')->latestOfMany();
    }

    /**
     * Kontrak yang sedang "menahan" kamar ini.
     *
     * Terdiri dari kontrak yang sudah aktif, atau booking berstatus pending
     * yang masih menunggu pembayaran (invoicenya belum lewat jatuh tempo).
     * Booking pending yang invoicenya sudah lewat jatuh tempo dianggap
     * ditinggalkan sehingga tidak lagi menahan kamar.
     */
    public function blockingLeases()
    {
        return $this->hasMany(Lease::class)->where(function ($q) {
            // Kontrak aktif menahan kamar, kecuali masa aktifnya sudah lewat
            // (menunggu ditutup otomatis oleh LeaseService::expireOverdueLeases).
            $q->where(function ($active) {
                $active->where('status', 'active')
                    ->where(function ($date) {
                        $date->whereNull('end_date')
                            ->orWhereDate('end_date', '>=', now()->toDateString());
                    });
            })
              ->orWhere(function ($pending) {
                  $pending->where('status', 'pending')
                      ->whereHas('payments', function ($p) {
                          $p->whereIn('status', ['unpaid', 'pending'])
                            ->whereDate('due_date', '>=', now()->toDateString());
                      });
              });
        });
    }

    /**
     * Apakah kamar masih dipegang kontrak aktif / booking yang belum dibayar.
     */
    public function hasBlockingLease(): bool
    {
        return $this->blockingLeases()->exists();
    }

    /**
     * Scope kamar yang benar-benar bebas dipesan publik
     * (aktif, status available, dan tidak sedang dipegang kontrak/booking lain).
     */
    public function scopeAvailableForBooking($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'available')
            ->whereDoesntHave('blockingLeases');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function getPricePerMonthAttribute()
    {
        return $this->price;
    }
}

