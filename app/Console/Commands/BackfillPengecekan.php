<?php

namespace App\Console\Commands;

use App\Models\DetailPengecekanMesin;
use App\Models\Mesin;
use App\Models\PengecekanMesin;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackfillPengecekan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pengecekan:backfill 
                            {--from= : Tanggal mulai (format: Y-m-d). Default: awal tahun ini}
                            {--to= : Tanggal selesai (format: Y-m-d). Default: kemarin}
                            {--mesin= : ID mesin spesifik. Default: semua mesin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill record pengecekan untuk rentang tanggal tertentu (untuk mesin yang belum ada data)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $fromDate = $this->option('from')
            ? Carbon::parse($this->option('from'))
            : Carbon::now()->startOfYear();

        $toDate = $this->option('to')
            ? Carbon::parse($this->option('to'))
            : Carbon::yesterday();

        $mesinId = $this->option('mesin');

        // Validasi tanggal
        if ($fromDate > $toDate) {
            $this->error('Tanggal mulai tidak boleh lebih besar dari tanggal selesai.');
            return self::FAILURE;
        }

        if ($toDate >= Carbon::today()) {
            $this->warn('Tanggal selesai tidak boleh hari ini atau masa depan. Menggunakan kemarin.');
            $toDate = Carbon::yesterday();
        }

        $this->info("Backfilling pengecekan records...");
        $this->info("From: {$fromDate->format('Y-m-d')} To: {$toDate->format('Y-m-d')}");

        // Ambil mesin
        $mesinsQuery = Mesin::with('komponenMesins');
        if ($mesinId) {
            $mesinsQuery->where('id', $mesinId);
        }
        $mesins = $mesinsQuery->get();

        if ($mesins->isEmpty()) {
            $this->warn('Tidak ada mesin yang ditemukan.');
            return self::SUCCESS;
        }

        // Generate period
        $period = CarbonPeriod::create($fromDate, $toDate);
        $totalDays = iterator_count($period);
        $period = CarbonPeriod::create($fromDate, $toDate); // Reset iterator

        $this->info("Total mesin: {$mesins->count()}, Total hari: {$totalDays}");

        $bar = $this->output->createProgressBar($totalDays * $mesins->count());
        $bar->start();

        $created = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($period as $date) {
                foreach ($mesins as $mesin) {
                    // Cek apakah sudah ada pengecekan
                    $exists = PengecekanMesin::where('mesin_id', $mesin->id)
                        ->whereDate('tanggal_pengecekan', $date)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    // Buat record
                    $pengecekan = PengecekanMesin::create([
                        'mesin_id' => $mesin->id,
                        'user_id' => $mesin->user_id,
                        'tanggal_pengecekan' => $date->startOfDay(),
                        'status' => 'tidak_dicek',
                    ]);

                    // Buat detail untuk setiap komponen
                    foreach ($mesin->komponenMesins as $komponen) {
                        DetailPengecekanMesin::create([
                            'pengecekan_mesin_id' => $pengecekan->id,
                            'komponen_mesin_id' => $komponen->id,
                            'status_sesuai' => 'tidak_dicek',
                            'keterangan' => null,
                        ]);
                    }

                    $created++;
                    $bar->advance();
                }
            }

            DB::commit();

            $bar->finish();
            $this->newLine(2);
            $this->info("Selesai! Created: {$created}, Skipped: {$skipped}");

            Log::info("BackfillPengecekan: {$fromDate->format('Y-m-d')} to {$toDate->format('Y-m-d')} - Created: {$created}, Skipped: {$skipped}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $bar->finish();
            $this->newLine();
            $this->error("Error: {$e->getMessage()}");
            Log::error("BackfillPengecekan failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
