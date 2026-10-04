<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lease extends Model
{
    //
    protected $fillable = [
        'tenant_id',
        'room_id',
        'start_date',
        'end_date',
        'checkout_date',
        'm_price',
        'monthly_price',
        'deposit_amount',
        'deposit_deduction',
        'deposit_refunded',
        'status',
        'room_condition',
        'note',
        'checkout_notes',
        'renewal_count',
        'last_renewed_at',
    ];


    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'checkout_date' => 'date',
        'last_renewed_at' => 'datetime',
        'deposit_deduction' => 'decimal:2',
        'deposit_refunded' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getMonthlyPriceAttribute()
    {
        return $this->m_price;
    }

    public function setMonthlyPriceAttribute($value)
    {
        $this->attributes['m_price'] = $value;
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->where('status', 'paid')->sum('amount');
    }

    public function getDepositPaidAttribute(): bool
    {
        if ($this->deposit_amount <= 0) {
            return false;
        }
        return $this->payments()
            ->where('status', 'paid')
            ->where(function ($q) {
                $q->where('amount', $this->deposit_amount)
                  ->orWhere('notes', 'like', '%deposit%')
                  ->orWhere('notes', 'like', '%uang muka%');
            })->exists();
    }

    public function getRemainingDueAttribute(): float
    {
        // Jika ada deposit_amount yang tercatat, sisa tagihan sewa berkurang sebesar deposit
        if ($this->deposit_amount > 0) {
            return (float) max(0, $this->m_price - $this->deposit_amount);
        }

        $paid = $this->total_paid;
        return (float) max(0, $this->m_price - $paid);
    }

    public function getNetRefundAmountAttribute(): float
    {
        return (float) max(0, (float) $this->deposit_amount - (float) $this->deposit_deduction);
    }

    public function isExpiringSoon(int $days = 14): bool
    {
        if ($this->status !== 'active' || !$this->end_date) {
            return false;
        }

        $now = now()->startOfDay();
        $endDate = $this->end_date->copy()->startOfDay();

        return $endDate->greaterThanOrEqualTo($now) && $endDate->diffInDays($now) <= $days;
    }

    public function isOverdue(): bool
    {
        if ($this->status !== 'active' || !$this->end_date) {
            return false;
        }

        return $this->end_date->copy()->startOfDay()->lessThan(now()->startOfDay());
    }

    /**
     * Sisa hari aktif sewa dihitung dari hari ini.
     */
    public function getRemainingDaysAttribute(): int
    {
        if (!$this->end_date) {
            return 0;
        }

        $now = now()->startOfDay();
        $endDate = $this->end_date->copy()->startOfDay();

        if ($endDate->lessThan($now)) {
            return 0;
        }

        return (int) $now->diffInDays($endDate);
    }
}

