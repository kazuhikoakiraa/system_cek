<?php

namespace Database\Factories;

use App\Models\Mesin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MesinFactory extends Factory
{
    protected $model = Mesin::class;

    public function definition(): array
    {
        return [
            'nama_mesin' => 'Mesin ' . fake()->randomElement(['CNC', 'Bubut', 'Las', 'Frais', 'Gerinda']) . ' ' . fake()->unique()->numberBetween(100, 999),
            'user_id' => User::role('operator')->inRandomOrder()->first()?->id ?? User::factory(),
            'deskripsi' => fake()->sentence(10),
        ];
    }
}
