<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MaintenanceReport>
 */
class MaintenanceReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'detail_pengecekan_mesin_id' => \App\Models\DetailPengecekanMesin::factory(),
            'mesin_id' => \App\Models\Mesin::factory(),
            'komponen_mesin_id' => \App\Models\KomponenMesin::factory(),
            'issue_description' => fake()->sentence(10),
            'status' => fake()->randomElement(['pending', 'in_progress', 'completed']),
            'foto_sebelum' => fake()->optional()->imageUrl(),
            'foto_sesudah' => fake()->optional()->imageUrl(),
            'catatan_teknisi' => fake()->optional()->paragraph(),
            'teknisi_id' => \App\Models\User::factory(),
            'tanggal_mulai' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'tanggal_selesai' => fake()->optional()->dateTimeBetween('now', '+1 week'),
        ];
    }
}
