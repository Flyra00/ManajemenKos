<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class GenerateMonthlyBillsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kos:generate-monthly-bills 
                            {--month= : Bulan tagihan (1-12, default: bulan saat ini)} 
                            {--year= : Tahun tagihan (contoh: 2026, default: tahun saat ini)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate tagihan sewa bulanan otomatis untuk seluruh kontrak sewa kos yang aktif';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService): int
    {
        $month = $this->option('month') ? (int) $this->option('month') : null;
        $year  = $this->option('year') ? (int) $this->option('year') : null;

        $this->info('Memulai pembuatan tagihan bulanan KosFly...');

        $result = $billingService->generateMonthlyBills($year, $month);

        $periodStr = $result['period']->translatedFormat('F Y');

        $this->table(
            ['Parameter', 'Keterangan'],
            [
                ['Periode Tagihan', $periodStr],
                ['Total Kontrak Aktif', $result['total']],
                ['Tagihan Berhasil Dibuat', $result['generated']],
                ['Dilewati (Sudah Ada)', $result['skipped']],
            ]
        );

        $this->info("Proses selesai: {$result['generated']} tagihan baru berhasil diterbitkan untuk periode {$periodStr}.");

        return Command::SUCCESS;
    }
}
