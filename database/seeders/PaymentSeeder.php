<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
                Payment::create([
            'lease_id' => 1,
            'invoice_number' => 'INV-202601-0001',
            'amount' => 800000,
            'billing_period' => '2026-01-01',
            'due_date' => '2026-01-25',
            'payment_date' => '2026-01-20 10:00:00',

            'payment_method' => 'bank_tf',

            'status' => 'paid',

            'proof_image' => 'payments/bukti-transfer.jpg',

            'verified_by' => 1,

            'notes' => 'Pembayaran bulan Januari',
        ]);
    }
}
