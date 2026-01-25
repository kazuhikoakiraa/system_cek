<?php

namespace App\Console\Commands;

use App\Models\DetailPengecekanMesin;
use App\Models\Mesin;
use App\Models\PengecekanMesin;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateDailyPengecekan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pengecekan:generate-daily 
                            {--date= : Tanggal spesifik (format: Y-m-d). Default: kemarin}
                            {--force : Paksa generate meskipun sudah ada data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate record pengecekan harian untuk semua mesin yang belum dicek';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $force = $this->option('force');

        $this->info("Generating pengecekan records for date: {$date->format('Y-m-d')}");

        // Ambil semua mesin
        $mesins = Mesin::with('komponenMesins')->get();

        if ($mesins->isEmpty()) {
            $this->warn('Tidak ada mesin yang terdaftar.');
            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;

        DB::beginTransaction();

        try {
            foreach ($mesins as $mesin) {
                // Cek apakah sudah ada pengecekan untuk tanggal ini
                $existingPengecekan = PengecekanMesin::where('mesin_id', $mesin->id)
                    ->whereDate('tanggal_pengecekan', $date)
                    ->first();

                if ($existingPengecekan) {
                    if ($force) {
                        // Jika force dan status masih dalam_proses, ubah ke tidak_dicek
                        if ($existingPengecekan->status === 'dalam_proses') {
                            $existingPengecekan->update(['status' => 'tidak_dicek']);
                            $this->line("  - {$mesin->nama_mesin}: Status diubah ke 'tidak_dicek'");
                            $created++;
                        } else {
                            $this->line("  - {$mesin->nama_mesin}: Sudah ada (status: {$existingPengecekan->status})");
                            $skipped++;
                        }
                    } else {
                        $this->line("  - {$mesin->nama_mesin}: Sudah ada data, dilewati");
                        $skipped++;
                    }
                    continue;
                }

                // Buat record pengecekan dengan status 'tidak_dicek'
                $pengecekan = PengecekanMesin::create([
                    'mesin_id' => $mesin->id,
                    'user_id' => $mesin->user_id, // Operator yang bertanggung jawab
                    'tanggal_pengecekan' => $date->startOfDay(),
                    'status' => 'tidak_dicek',
                ]);

                // Buat detail pengecekan untuk setiap komponen dengan status null/tidak_dicek
                foreach ($mesin->komponenMesins as $komponen) {
                    DetailPengecekanMesin::create([
                        'pengecekan_mesin_id' => $pengecekan->id,
                        'komponen_mesin_id' => $komponen->id,
                        'status_sesuai' => 'tidak_dicek',
                        'keterangan' => null,
                    ]);
                }

                $this->line("  ✓ {$mesin->nama_mesin}: Record created (tidak_dicek)");
                $created++;
            }

            DB::commit();

            $this->newLine();
            $this->info("Selesai! Created: {$created}, Skipped: {$skipped}");

            Log::info("GenerateDailyPengecekan: Date {$date->format('Y-m-d')} - Created: {$created}, Skipped: {$skipped}");

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: {$e->getMessage()}");
            Log::error("GenerateDailyPengecekan failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
