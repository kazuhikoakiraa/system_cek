<?php

namespace Database\Seeders;

use App\Models\Mesin;
use App\Models\KomponenMesin;
use App\Models\User;
use Illuminate\Database\Seeder;

class MesinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada user dengan role operator
        $operators = User::role('Operator')->get();
        
        if ($operators->isEmpty()) {
            $this->command->warn('Tidak ada user dengan role operator. Membuat 3 operator...');
            for ($i = 1; $i <= 3; $i++) {
                $user = User::create([
                    'name' => "Operator $i",
                    'email' => "operator$i@example.com",
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]);
                $user->assignRole('Operator');
                $operators->push($user);
            }
        }

        // Create 10 sample machines with components
        foreach ($operators->take(3) as $index => $operator) {
            // Buat 2-3 mesin per operator
            $mesinCount = rand(2, 3);
            
            for ($i = 1; $i <= $mesinCount; $i++) {
                $mesin = Mesin::create([
                    'nama_mesin' => 'Mesin ' . fake()->randomElement(['CNC', 'Bubut', 'Las', 'Frais', 'Gerinda']) . ' ' . fake()->unique()->numberBetween(100, 999),
                    'user_id' => $operator->id,
                    'deskripsi' => fake()->sentence(10),
                ]);

                // Tambahkan 2-5 komponen per mesin
                $komponenCount = rand(2, 5);
                $komponenList = [
                    'Motor Penggerak',
                    'Bearing Utama',
                    'Belt Conveyor',
                    'Gearbox',
                    'Pompa Hidrolik',
                    'Sensor Suhu',
                    'Cooling System',
                    'Spindle',
                    'Chuck 3-Jaw',
                    'Tailstock',
                ];

                $standarList = [
                    'Tekanan 5-7 bar, Suhu maksimal 80°C',
                    'Grease kuning, tidak ada bunyi abnormal',
                    'Tidak ada keretakan, isolasi masih baik',
                    'Tegangan stabil 220V ± 10V',
                    'Getaran < 0.5mm/s',
                    'Temperatur operasional 40-60°C',
                    'Tidak ada kebocoran oli',
                    'Torsi sesuai spesifikasi manual',
                    'Grip kuat, tidak ada slip',
                    'Alignment presisi < 0.01mm',
                ];

                for ($j = 0; $j < $komponenCount; $j++) {
                    KomponenMesin::create([
                        'mesin_id' => $mesin->id,
                        'nama_komponen' => $komponenList[array_rand($komponenList)],
                        'standar' => $standarList[array_rand($standarList)],
                        'frekuensi' => fake()->randomElement(['harian', 'mingguan', 'bulanan', 'tahunan']),
                        'catatan' => fake()->boolean(30) ? fake()->sentence() : null,
                    ]);
                }
            }
        }

        $this->command->info('Sample mesin data created successfully!');
        $this->command->info('Total Mesin: ' . Mesin::count());
        $this->command->info('Total Komponen: ' . KomponenMesin::count());
    }
}
