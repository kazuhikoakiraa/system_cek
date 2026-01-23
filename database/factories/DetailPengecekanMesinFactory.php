<?php

namespace Database\Factories;

use App\Models\KomponenMesin;
use App\Models\PengecekanMesin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetailPengecekanMesin>
 */
class DetailPengecekanMesinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statusSesuai = fake()->randomElement(['sesuai', 'tidak_sesuai']);
        
        return [
            'pengecekan_mesin_id' => PengecekanMesin::factory(),
            'komponen_mesin_id' => KomponenMesin::factory(),
            'status_sesuai' => $statusSesuai,
            'keterangan' => $statusSesuai === 'tidak_sesuai' ? fake()->sentence() : null,
        ];
    }
}
