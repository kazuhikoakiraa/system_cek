<?php

namespace Database\Seeders;

use App\Models\DetailPengecekanMesin;
use App\Models\Mesin;
use App\Models\PengecekanMesin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PengecekanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua mesin yang ada
        $mesins = Mesin::with('komponenMesins', 'operator')->get();

        if ($mesins->isEmpty()) {
            $this->command->warn('Tidak ada mesin yang tersedia. Jalankan MesinSeeder terlebih dahulu.');
            return;
        }

        // Buat pengecekan untuk setiap mesin
        foreach ($mesins as $mesin) {
            // Buat 5 pengecekan untuk mesin ini (5 hari terakhir)
            for ($i = 0; $i < 5; $i++) {
                $tanggal = Carbon::today()->subDays($i);

                // Buat pengecekan
                $pengecekan = PengecekanMesin::create([
                    'mesin_id' => $mesin->id,
                    'user_id' => $mesin->user_id,
                    'tanggal_pengecekan' => $tanggal,
                    'status' => 'selesai',
                ]);

                // Buat detail pengecekan untuk setiap komponen
                foreach ($mesin->komponenMesins as $komponen) {
                    $statusSesuai = fake()->randomElement(['sesuai', 'sesuai', 'sesuai', 'tidak_sesuai']); // 75% sesuai, 25% tidak sesuai

                    DetailPengecekanMesin::create([
                        'pengecekan_mesin_id' => $pengecekan->id,
                        'komponen_mesin_id' => $komponen->id,
                        'status_sesuai' => $statusSesuai,
                        'keterangan' => $statusSesuai === 'tidak_sesuai' 
                            ? fake()->sentence() 
                            : null,
                    ]);
                }
            }

            $this->command->info("Berhasil membuat 5 pengecekan untuk mesin: {$mesin->nama_mesin}");
        }
    }
}
