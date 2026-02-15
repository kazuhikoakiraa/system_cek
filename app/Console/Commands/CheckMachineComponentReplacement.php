<?php

namespace App\Console\Commands;

use App\Models\MComponent;
use App\Models\Mesin;
use App\Notifications\ComponentReplacementReminder;
use App\Notifications\MachineReplacementReminder;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckMachineComponentReplacement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'machine:check-replacement
                            {--days=30 : Number of days to check ahead}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cek komponen dan mesin yang perlu diganti dalam waktu dekat';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $this->info("Mengecek komponen dan mesin yang perlu diganti dalam {$days} hari ke depan...");

        $this->checkComponents($days);
        $this->checkMachines($days);

        $this->info('Selesai!');
        return 0;
    }

    protected function checkComponents($days)
    {
        $targetDate = Carbon::now()->addDays($days);
        
        // Komponen yang sudah melewati tanggal penggantian
        $overdueComponents = MComponent::whereNotNull('estimasi_tanggal_ganti_berikutnya')
            ->where('estimasi_tanggal_ganti_berikutnya', '<', Carbon::now())
            ->where('status_komponen', '!=', 'rusak')
            ->with(['mesin.pemilik'])
            ->get();

        // Komponen yang akan jatuh tempo dalam X hari
        $upcomingComponents = MComponent::whereNotNull('estimasi_tanggal_ganti_berikutnya')
            ->whereBetween('estimasi_tanggal_ganti_berikutnya', [Carbon::now(), $targetDate])
            ->with(['mesin.pemilik'])
            ->get();

        $this->info("Komponen terlambat: {$overdueComponents->count()}");
        $this->info("Komponen akan jatuh tempo: {$upcomingComponents->count()}");

        foreach ($overdueComponents as $component) {
            $this->warn("  ⚠️ OVERDUE: {$component->nama_komponen} - Mesin: {$component->mesin->nama_mesin}");
            
            // Kirim notifikasi ke penanggung jawab mesin
            if ($component->mesin->pemilik) {
                $component->mesin->pemilik->notify(new ComponentReplacementReminder($component, true));
            }

            // Update status komponen
            if ($component->status_komponen !== 'perlu_ganti') {
                $component->update(['status_komponen' => 'perlu_ganti']);
            }
        }

        foreach ($upcomingComponents as $component) {
            $daysLeft = Carbon::now()->diffInDays($component->estimasi_tanggal_ganti_berikutnya);
            $this->info("  ⏰ {$component->nama_komponen} - {$daysLeft} hari lagi - Mesin: {$component->mesin->nama_mesin}");
            
            // Kirim notifikasi ke penanggung jawab mesin
            if ($component->mesin->pemilik) {
                $component->mesin->pemilik->notify(new ComponentReplacementReminder($component, false));
            }
        }
    }

    protected function checkMachines($days)
    {
        $targetDate = Carbon::now()->addDays($days);
        
        // Mesin yang sudah melewati estimasi penggantian
        $overdueMachines = Mesin::whereNotNull('estimasi_penggantian')
            ->where('estimasi_penggantian', '<', Carbon::now())
            ->where('status', '!=', 'rusak')
            ->with('pemilik')
            ->get();

        // Mesin yang akan jatuh tempo dalam X hari
        $upcomingMachines = Mesin::whereNotNull('estimasi_penggantian')
            ->whereBetween('estimasi_penggantian', [Carbon::now(), $targetDate])
            ->with('pemilik')
            ->get();

        $this->info("Mesin terlambat penggantian: {$overdueMachines->count()}");
        $this->info("Mesin akan jatuh tempo penggantian: {$upcomingMachines->count()}");

        foreach ($overdueMachines as $mesin) {
            $this->warn("  ⚠️ OVERDUE: {$mesin->nama_mesin} ({$mesin->kode_mesin})");
            
            // Kirim notifikasi ke penanggung jawab mesin
            if ($mesin->pemilik) {
                $mesin->pemilik->notify(new MachineReplacementReminder($mesin, true));
            }
        }

        foreach ($upcomingMachines as $mesin) {
            $daysLeft = Carbon::now()->diffInDays($mesin->estimasi_penggantian);
            $this->info("  ⏰ {$mesin->nama_mesin} ({$mesin->kode_mesin}) - {$daysLeft} hari lagi");
            
            // Kirim notifikasi ke penanggung jawab mesin
            if ($mesin->pemilik) {
                $mesin->pemilik->notify(new MachineReplacementReminder($mesin, false));
            }
        }
    }
}
