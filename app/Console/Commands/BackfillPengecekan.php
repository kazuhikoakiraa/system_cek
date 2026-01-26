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
    protected $description = 'Backfill record pengecekan (command ini sudah tidak diperlukan karena "tidak dicek" = "tidak ada data")';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('Command ini sudah tidak diperlukan lagi!');
        $this->info('Status "tidak dicek" sekarang sama dengan "tidak ada data pengecekan".');
        $this->info('Tidak perlu lagi membuat record pengecekan untuk mesin yang tidak dicek.');
        
        return self::SUCCESS;
    }
}
