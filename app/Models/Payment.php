<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    protected $fillable = [
        "tenant_id",
        'invoice_number',
        'amount',
        'billing_period',
        'due_date',
        'payment_date',
        'payment_method',
        'status',
        'proof_img',
        'verified_by',
        'notes',
    ];

    protected $casts = [
        'billing_period' => 'date',
        'due_date' => 'date',
        'payment_date' => 'datetime',
    ];

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
