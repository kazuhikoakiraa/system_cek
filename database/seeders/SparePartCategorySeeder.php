<?php

namespace Database\Seeders;

use App\Models\SparePartCategory;
use Illuminate\Database\Seeder;

class SparePartCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'kode_kategori' => 'ELEC',
                'nama_kategori' => 'Electrical',
                'deskripsi' => 'Komponen dan spare part elektrikal',
            ],
            [
                'kode_kategori' => 'MECH',
                'nama_kategori' => 'Mechanical',
                'deskripsi' => 'Komponen dan spare part mekanikal',
            ],
            [
                'kode_kategori' => 'HYDR',
                'nama_kategori' => 'Hydraulic',
                'deskripsi' => 'Komponen dan spare part hydraulic',
            ],
            [
                'kode_kategori' => 'PNEU',
                'nama_kategori' => 'Pneumatic',
                'deskripsi' => 'Komponen dan spare part pneumatic',
            ],
            [
                'kode_kategori' => 'CONS',
                'nama_kategori' => 'Consumable',
                'deskripsi' => 'Barang habis pakai (oli, grease, seal, dll)',
            ],
            [
                'kode_kategori' => 'BEAR',
                'nama_kategori' => 'Bearing',
                'deskripsi' => 'Semua jenis bearing',
            ],
            [
                'kode_kategori' => 'BELT',
                'nama_kategori' => 'Belt & Chain',
                'deskripsi' => 'V-belt, timing belt, chain, dll',
            ],
            [
                'kode_kategori' => 'FILT',
                'nama_kategori' => 'Filter',
                'deskripsi' => 'Filter oli, udara, hydraulic, dll',
            ],
            [
                'kode_kategori' => 'TOOL',
                'nama_kategori' => 'Tools',
                'deskripsi' => 'Perkakas dan tools',
            ],
            [
                'kode_kategori' => 'SAFE',
                'nama_kategori' => 'Safety Equipment',
                'deskripsi' => 'Alat pelindung diri (APD)',
            ],
        ];

        foreach ($categories as $category) {
            SparePartCategory::create($category);
        }
    }
}
