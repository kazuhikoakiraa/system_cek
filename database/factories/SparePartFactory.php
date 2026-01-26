<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SparePart>
 */
class SparePartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_suku_cadang' => fake()->unique()->bothify('SP-####-??'),
            'nama_suku_cadang' => fake()->words(3, true),
            'deskripsi' => fake()->optional()->sentence(),
            'stok' => fake()->numberBetween(0, 100),
            'satuan' => fake()->randomElement(['pcs', 'unit', 'set', 'box', 'pack']),
        ];
    }
}
