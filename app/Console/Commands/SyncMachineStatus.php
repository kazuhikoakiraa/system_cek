<?php

namespace App\Console\Commands;

use App\Models\Mesin;
use App\Models\MRequest;
use Illuminate\Console\Command;

class SyncMachineStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'machine:sync-status
                            {--dry-run : Tampilkan perubahan tanpa menyimpan ke database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronkan status master mesin berdasarkan request maintenance aktif';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $this->info($isDryRun
            ? 'Memulai sinkronisasi status mesin (dry-run)...'
            : 'Memulai sinkronisasi status mesin...');

        $updatedCount = 0;
        $totalCount = 0;

        Mesin::query()
            ->select('id', 'status')
            ->orderBy('id')
            ->chunkById(200, function ($mesins) use (&$updatedCount, &$totalCount, $isDryRun) {
                foreach ($mesins as $mesin) {
                    $totalCount++;

                    $before = $mesin->status;
                    $hasActiveRequest = MRequest::query()
                        ->where('mesin_id', $mesin->id)
                        ->activeForMachineStatus()
                        ->exists();
                    $after = $hasActiveRequest ? 'maintenance' : 'aktif';

                    if ($before !== $after) {
                        $updatedCount++;

                        if (! $isDryRun) {
                            MRequest::syncMachineStatus($mesin->id);
                        }
                    }
                }
            });

        $this->info("Total mesin diproses: {$totalCount}");
        $this->info("Status mesin berubah: {$updatedCount}");
        $this->info($isDryRun
            ? 'Dry-run selesai. Tidak ada perubahan data yang disimpan.'
            : 'Sinkronisasi selesai.');

        return self::SUCCESS;
    }
}
