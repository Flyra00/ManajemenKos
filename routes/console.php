<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal otomatis pembuatan tagihan sewa bulanan setiap tanggal 1 pukul 00:05
\Illuminate\Support\Facades\Schedule::command('kos:generate-monthly-bills')->monthlyOn(1, '00:05');

// Tutup otomatis kontrak sewa yang masa aktifnya sudah berakhir & bebaskan kamarnya setiap hari
\Illuminate\Support\Facades\Schedule::command('kos:expire-leases')->dailyAt('00:10');
