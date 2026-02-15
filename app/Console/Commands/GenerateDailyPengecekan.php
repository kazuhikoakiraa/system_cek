<?php

namespace App\Console\Commands;

use App\Models\DetailPengecekanMesin;
use App\Models\DaftarPengecekan;
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
    protected $description = 'Generate record pengecekan harian untuk semua mesin (command ini sudah tidak diperlukan karena "tidak dicek" = "tidak ada data")';

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
