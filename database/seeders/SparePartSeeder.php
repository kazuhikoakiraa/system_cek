<?php

namespace Database\Seeders;

use App\Models\SparePart;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SparePartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $spareParts = [
            [
                'kode_suku_cadang' => 'SP-001',
                'nama_suku_cadang' => 'Bearing 6205',
                'deskripsi' => 'Bearing tipe 6205 untuk motor mesin',
                'stok' => 50,
                'satuan' => 'pcs',
            ],
            [
                'kode_suku_cadang' => 'SP-002',
                'nama_suku_cadang' => 'V-Belt A40',
                'deskripsi' => 'V-Belt tipe A dengan panjang 40 inch',
                'stok' => 30,
                'satuan' => 'pcs',
            ],
            [
                'kode_suku_cadang' => 'SP-003',
                'nama_suku_cadang' => 'Oli SAE 40',
                'deskripsi' => 'Oli mesin SAE 40',
                'stok' => 100,
                'satuan' => 'liter',
            ],
            [
                'kode_suku_cadang' => 'SP-004',
                'nama_suku_cadang' => 'Filter Udara',
                'deskripsi' => 'Filter udara standar untuk kompresor',
                'stok' => 25,
                'satuan' => 'pcs',
            ],
            [
                'kode_suku_cadang' => 'SP-005',
                'nama_suku_cadang' => 'Seal O-Ring 20mm',
                'deskripsi' => 'Seal O-Ring dengan diameter 20mm',
                'stok' => 200,
                'satuan' => 'pcs',
            ],
            [
                'kode_suku_cadang' => 'SP-006',
                'nama_suku_cadang' => 'Baut M12x50',
                'deskripsi' => 'Baut diameter 12mm panjang 50mm',
                'stok' => 500,
                'satuan' => 'pcs',
            ],
            [
                'kode_suku_cadang' => 'SP-007',
                'nama_suku_cadang' => 'Grease Lithium',
                'deskripsi' => 'Grease lithium untuk pelumasan',
                'stok' => 15,
                'satuan' => 'pack',
            ],
            [
                'kode_suku_cadang' => 'SP-008',
                'nama_suku_cadang' => 'Switch Limit',
                'deskripsi' => 'Limit switch untuk sensor posisi',
                'stok' => 20,
                'satuan' => 'pcs',
            ],
        ];

        foreach ($spareParts as $sparePart) {
            SparePart::create($sparePart);
        }
    }
}
