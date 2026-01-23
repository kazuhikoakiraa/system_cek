<?php

namespace Database\Factories;

use App\Models\KomponenMesin;
use App\Models\Mesin;
use Illuminate\Database\Eloquent\Factories\Factory;

class KomponenMesinFactory extends Factory
{
    protected $model = KomponenMesin::class;

    public function definition(): array
    {
        $komponenList = [
            'Motor Penggerak',
            'Bearing Utama',
            'Belt Conveyor',
            'Gearbox',
            'Kabel Las',
            'Pompa Hidrolik',
            'Sensor Suhu',
            'Cooling System',
            'Spindle',
            'Chuck 3-Jaw',
            'Tailstock',
            'Tool Turret',
            'Coolant Pump',
            'Control Panel',
            'Emergency Stop'
        ];

        $standarList = [
            'Tekanan 5-7 bar, Suhu maksimal 80°C',
            'Grease harus berwarna kuning, tidak ada bunyi abnormal',
            'Tidak ada keretakan, isolasi masih baik',
            'Tegangan stabil 220V ± 10V',
            'Getaran < 0.5mm/s',
            'Temperatur operasional 40-60°C',
            'Tidak ada kebocoran oli',
            'Torsi sesuai spesifikasi manual',
            'Grip kuat, tidak ada slip',
            'Alignment presisi < 0.01mm',
            'Response time < 1 detik',
            'Flow rate 10-15 L/min'
        ];

        return [
            'mesin_id' => Mesin::factory(),
            'nama_komponen' => fake()->randomElement($komponenList),
            'standar' => fake()->randomElement($standarList),
            'frekuensi' => fake()->randomElement(['harian', 'mingguan', 'bulanan', 'tahunan']),
            'catatan' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }
}
