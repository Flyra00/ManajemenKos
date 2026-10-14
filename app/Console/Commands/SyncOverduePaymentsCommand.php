<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;

class SyncOverduePaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kos:sync-overdue-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi otomatis status tagihan sewa yang melewati tanggal jatuh tempo menjadi overdue';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = Payment::where('status', 'unpaid')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $this->info("Selesai: {$updated} tagihan yang melewati jatuh tempo berhasil disinkronkan menjadi overdue.");

        return Command::SUCCESS;
    }
}
