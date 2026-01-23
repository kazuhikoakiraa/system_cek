<?php

namespace Database\Factories;

use App\Models\Mesin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PengecekanMesin>
 */
class PengecekanMesinFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mesin_id' => Mesin::factory(),
            'user_id' => User::factory(),
            'tanggal_pengecekan' => fake()->date(),
            'status' => fake()->randomElement(['selesai', 'dalam_proses']),
        ];
    }
}
