<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Payment extends Model
{
    //
    protected $fillable = [
        'lease_id',
        'invoice_number',

        'amount',
        'billing_period',
        'due_date',
        'payment_date',
        'payment_method',
        'status',
        'proof_img',
        'snap_token',
        'midtrans_response',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'billing_period' => 'date',
        'due_date' => 'date',
        'payment_date' => 'datetime',
        'midtrans_response' => 'array',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * URL publik bertanda tangan untuk membuka invoice ini.
     *
     * Nomor invoice tidak boleh dipakai sebagai kunci akses mentah karena bisa
     * dienumerasi. Gunakan accessor ini (atau URL::signedRoute) di semua tempat,
     * bukan route('invoices.show', ...) langsung.
     */
    public function getPublicUrlAttribute(): string
    {
        return URL::signedRoute('invoices.show', ['invoice_number' => $this->invoice_number]);
    }

    /**
     * URL publik bertanda tangan untuk mencetak kuitansi/invoice resmi.
     */
    public function getPublicReceiptUrlAttribute(): string
    {
        return URL::signedRoute('invoices.receipt', ['invoice_number' => $this->invoice_number]);
    }

    public function getProofImageAttribute(): ?string
    {
        return $this->attributes['proof_img'] ?? null;
    }

    public function setProofImageAttribute(?string $value): void
    {
        $this->attributes['proof_img'] = $value;
    }
}

