<?php

namespace App\Console\Commands;

use App\Services\LeaseService;
use Illuminate\Console\Command;

class ExpireOverdueLeasesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kos:expire-leases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menutup otomatis kontrak sewa yang masa aktifnya sudah berakhir dan membebaskan kamarnya';

    /**
     * Execute the console command.
     */
    public function handle(LeaseService $leaseService): int
    {
        $closed = $leaseService->expireOverdueLeases();

        $this->info("Selesai: {$closed} kontrak sewa ditutup otomatis dan kamarnya dibebaskan.");

        return Command::SUCCESS;
    }
}
