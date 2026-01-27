<?php

namespace Database\Seeders;

use App\Models\DetailPengecekanMesin;
use App\Models\Mesin;
use App\Models\PengecekanMesin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PengecekanJanuari2026Seeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Seeder ini akan membuat data pengecekan dummy untuk tanggal 1-25 Januari 2026
     * dengan semua status komponen "sesuai" (compliant)
     */
    public function run(): void
    {
        $this->command->info('Memulai seeding pengecekan Januari 2026...');

        // Ambil semua mesin yang ada
        $mesins = Mesin::with('komponenMesins', 'operator')->get();

        if ($mesins->isEmpty()) {
            $this->command->warn('Tidak ada mesin yang tersedia. Jalankan MesinSeeder terlebih dahulu.');
            return;
        }

        $totalPengecekan = 0;
        $totalDetail = 0;

        // Loop untuk setiap mesin
        foreach ($mesins as $mesin) {
            $this->command->info("Processing mesin: {$mesin->nama_mesin}");

            // Buat pengecekan untuk tanggal 1-25 Januari 2026
            for ($day = 1; $day <= 25; $day++) {
                // Buat tanggal pengecekan dengan jam acak antara 07:00 - 16:00
                $tanggal = Carbon::create(2026, 1, $day)->setTime(
                    rand(7, 16),  // Hour between 7am-4pm
                    rand(0, 59),  // Minute
                    rand(0, 59)   // Second
                );

                // Cek apakah pengecekan sudah ada untuk mesin dan tanggal ini
                $exists = PengecekanMesin::where('mesin_id', $mesin->id)
                    ->whereDate('tanggal_pengecekan', $tanggal->format('Y-m-d'))
                    ->exists();

                if ($exists) {
                    $this->command->warn("  - Pengecekan untuk {$mesin->nama_mesin} tanggal {$tanggal->format('Y-m-d')} sudah ada, dilewati.");
                    continue;
                }

                // Buat pengecekan
                $pengecekan = PengecekanMesin::create([
                    'mesin_id' => $mesin->id,
                    'user_id' => $mesin->user_id,
                    'tanggal_pengecekan' => $tanggal,
                    'status' => 'selesai',
                ]);

                $totalPengecekan++;

                // Buat detail pengecekan untuk setiap komponen dengan status "sesuai"
                foreach ($mesin->komponenMesins as $komponen) {
                    DetailPengecekanMesin::create([
                        'pengecekan_mesin_id' => $pengecekan->id,
                        'komponen_mesin_id' => $komponen->id,
                        'status_sesuai' => 'sesuai', // Semua status sesuai
                        'keterangan' => null, // Tidak ada keterangan karena semua sesuai
                    ]);

                    $totalDetail++;
                }
            }

            $this->command->info("  ✓ Selesai untuk mesin: {$mesin->nama_mesin}");
        }

        $this->command->info('');
        $this->command->info("========================================");
        $this->command->info("Seeding selesai!");
        $this->command->info("Total pengecekan dibuat: {$totalPengecekan}");
        $this->command->info("Total detail pengecekan dibuat: {$totalDetail}");
        $this->command->info("Periode: 1-25 Januari 2026");
        $this->command->info("Status: Semua komponen SESUAI");
        $this->command->info("========================================");
    }
}
