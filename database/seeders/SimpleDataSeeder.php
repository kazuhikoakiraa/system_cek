<?php

namespace Database\Seeders;

use App\Models\Mesin;
use App\Models\MComponent;
use App\Models\DaftarPengecekan;
use App\Models\KomponenDaftarPengecekan;
use App\Models\SparePart;
use App\Models\SparePartCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SimpleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Starting Simple Data Seeder...');

        // Pastikan ada user dengan role yang diperlukan
        $admin = User::role('Super Admin')->first() ?? User::role('Admin')->first();
        
        // Create 5 operators for 5 machines and 5 daftar pengecekan
        $this->command->info('👥 Creating 5 Operators...');
        $operators = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $operator = User::firstOrCreate(
                ['email' => "operator$i@system-cek.com"],
                [
                    'name' => "Operator Produksi $i",
                    'password' => bcrypt('password'),
                    'employee_id' => sprintf('OP%03d', $i),
                    'department' => 'Produksi',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            
            if (!$operator->hasRole('Operator')) {
                $operator->assignRole('Operator');
            }
            
            $operators[] = $operator;
            $this->command->info("  ✅ Created: {$operator->name} ({$operator->email})");
        }

        // 1. CREATE 5 MESIN (MANAJEMEN MESIN)
        $this->command->info('📦 Creating 5 Mesin...');
        
        $mesins = [
            [
                'kode_mesin' => 'MSN-001',
                'serial_number' => 'SN-2020-001',
                'nama_mesin' => 'Mesin CNC Milling XYZ-500',
                'manufacturer' => 'Haas Automation',
                'model_number' => 'VF-2',
                'tahun_pembuatan' => 2020,
                'jenis_mesin' => 'CNC Milling',
                'supplier' => 'PT Mesin Industri',
                'tanggal_pengadaan' => Carbon::create(2020, 3, 15),
                'harga_pengadaan' => 350000000,
                'status' => 'aktif',
                'kondisi_terakhir' => 'Baik',
                'user_id' => null, // Will be assigned later
            ],
            [
                'kode_mesin' => 'MSN-002',
                'serial_number' => 'SN-2019-045',
                'nama_mesin' => 'Mesin Bubut Konvensional',
                'manufacturer' => 'DMG Mori',
                'model_number' => 'CLX-350',
                'tahun_pembuatan' => 2019,
                'jenis_mesin' => 'Bubut',
                'supplier' => 'CV Teknik Jaya',
                'tanggal_pengadaan' => Carbon::create(2019, 7, 20),
                'harga_pengadaan' => 180000000,
                'status' => 'aktif',
                'kondisi_terakhir' => 'Baik',
                'user_id' => null, // Will be assigned later
            ],
            [
                'kode_mesin' => 'MSN-003',
                'serial_number' => 'SN-2021-078',
                'nama_mesin' => 'Mesin Las Otomatis',
                'manufacturer' => 'Lincoln Electric',
                'model_number' => 'PowerWave S500',
                'tahun_pembuatan' => 2021,
                'jenis_mesin' => 'Welding',
                'supplier' => 'PT Welding Supply',
                'tanggal_pengadaan' => Carbon::create(2021, 2, 10),
                'harga_pengadaan' => 125000000,
                'status' => 'aktif',
                'kondisi_terakhir' => 'Baik',
                'user_id' => null, // Will be assigned later
            ],
            [
                'kode_mesin' => 'MSN-004',
                'serial_number' => 'SN-2018-112',
                'nama_mesin' => 'Mesin Gerinda Permukaan',
                'manufacturer' => 'Okamoto',
                'model_number' => 'ACC-84EX',
                'tahun_pembuatan' => 2018,
                'jenis_mesin' => 'Grinding',
                'supplier' => 'PT Precision Tools',
                'tanggal_pengadaan' => Carbon::create(2018, 11, 5),
                'harga_pengadaan' => 95000000,
                'status' => 'maintenance',
                'kondisi_terakhir' => 'Perlu Perbaikan',
                'user_id' => null, // Will be assigned later
            ],
            [
                'kode_mesin' => 'MSN-005',
                'serial_number' => 'SN-2022-203',
                'nama_mesin' => 'Mesin Press Hidrolik 100 Ton',
                'manufacturer' => 'Schuler',
                'model_number' => 'HPM-100',
                'tahun_pembuatan' => 2022,
                'jenis_mesin' => 'Hydraulic Press',
                'supplier' => 'PT Hidrolik Indonesia',
                'tanggal_pengadaan' => Carbon::create(2022, 5, 18),
                'harga_pengadaan' => 420000000,
                'status' => 'aktif',
                'kondisi_terakhir' => 'Sangat Baik',
                'user_id' => null, // Will be assigned later
            ],
        ];

        $createdMesins = [];
        foreach ($mesins as $index => $mesinData) {
            // Assign each machine to a different operator
            $mesinData['user_id'] = $operators[$index]->id;
            $mesin = Mesin::create($mesinData);
            $createdMesins[] = $mesin;
            $this->command->info("  ✅ Created: {$mesin->nama_mesin} (Operator: {$operators[$index]->name})");
        }

        // 2. CREATE KOMPONEN MESIN (M_COMPONENTS)
        $this->command->info('🔧 Creating Komponen Mesin...');
        
        $komponenData = [
            // Komponen untuk Mesin CNC
            [
                'mesin_id' => $createdMesins[0]->id,
                'nama_komponen' => 'Spindle Motor',
                'manufacturer' => 'Fanuc',
                'part_number' => 'A06B-0235-B605',
                'spesifikasi_teknis' => '7.5 kW, 8000 RPM',
                'jadwal_ganti_bulan' => 36,
                'status_komponen' => 'normal',
                'lokasi_pemasangan' => 'Main Spindle Unit',
            ],
            [
                'mesin_id' => $createdMesins[0]->id,
                'nama_komponen' => 'Ball Screw X-Axis',
                'manufacturer' => 'THK',
                'part_number' => 'BNK2505-3.6',
                'spesifikasi_teknis' => 'Diameter 25mm, Lead 5mm',
                'jadwal_ganti_bulan' => 48,
                'status_komponen' => 'normal',
                'lokasi_pemasangan' => 'X-Axis Drive',
            ],
            // Komponen untuk Mesin Bubut
            [
                'mesin_id' => $createdMesins[1]->id,
                'nama_komponen' => 'Chuck 3-Jaw',
                'manufacturer' => 'Bison',
                'part_number' => '3-JAW-200',
                'spesifikasi_teknis' => '200mm diameter, Through-hole 52mm',
                'jadwal_ganti_bulan' => 60,
                'status_komponen' => 'normal',
                'lokasi_pemasangan' => 'Main Spindle',
            ],
            // Komponen untuk Mesin Las
            [
                'mesin_id' => $createdMesins[2]->id,
                'nama_komponen' => 'Welding Torch',
                'manufacturer' => 'Bernard',
                'part_number' => 'Q-Gun-400A',
                'spesifikasi_teknis' => '400 Ampere, Water-cooled',
                'jadwal_ganti_bulan' => 12,
                'status_komponen' => 'normal',
                'lokasi_pemasangan' => 'Front Panel',
            ],
            // Komponen untuk Mesin Gerinda
            [
                'mesin_id' => $createdMesins[3]->id,
                'nama_komponen' => 'Grinding Wheel',
                'manufacturer' => 'Norton',
                'part_number' => 'GW-A60-400',
                'spesifikasi_teknis' => '400mm x 40mm, A60 Grit',
                'jadwal_ganti_bulan' => 6,
                'status_komponen' => 'perlu_ganti',
                'lokasi_pemasangan' => 'Grinding Head',
            ],
        ];

        foreach ($komponenData as $komponen) {
            MComponent::create($komponen);
            $this->command->info("  ✅ Created: {$komponen['nama_komponen']}");
        }

        // 3. CREATE 5 DAFTAR PENGECEKAN (MANAJEMEN PENGECEKAN)
        $this->command->info('📋 Creating 5 Daftar Pengecekan...');
        
        $daftarPengecekan = [
            [
                'nama_mesin' => 'Pengecekan Harian Mesin CNC',
                'user_id' => null, // Will be assigned later
                'deskripsi' => 'Pengecekan rutin harian untuk mesin CNC di lantai produksi 1',
            ],
            [
                'nama_mesin' => 'Pengecekan Mingguan Mesin Bubut',
                'user_id' => null, // Will be assigned later
                'deskripsi' => 'Inspeksi mingguan kondisi mesin bubut dan komponennya',
            ],
            [
                'nama_mesin' => 'Pengecekan Mesin Las Workshop A',
                'user_id' => null, // Will be assigned later
                'deskripsi' => 'Pengecekan keselamatan dan performa mesin las otomatis',
            ],
            [
                'nama_mesin' => 'Pengecekan Mesin Gerinda',
                'user_id' => null, // Will be assigned later
                'deskripsi' => 'Pengecekan roda gerinda dan sistem pendingin',
            ],
            [
                'nama_mesin' => 'Pengecekan Press Hidrolik',
                'user_id' => null, // Will be assigned later
                'deskripsi' => 'Monitoring tekanan hidrolik dan sistem keamanan',
            ],
        ];

        $createdDaftarPengecekan = [];
        foreach ($daftarPengecekan as $index => $daftar) {
            // Assign each daftar pengecekan to a different operator (1 operator = 1 daftar pengecekan)
            $daftar['user_id'] = $operators[$index]->id;
            $item = DaftarPengecekan::create($daftar);
            $createdDaftarPengecekan[] = $item;
            $this->command->info("  ✅ Created: {$item->nama_mesin} (Operator: {$operators[$index]->name})");
        }

        // 4. CREATE KOMPONEN DAFTAR PENGECEKAN
        $this->command->info('🔍 Creating Komponen Daftar Pengecekan...');
        
        $komponenPengecekan = [
            // Untuk Pengecekan CNC
            [
                'mesin_id' => $createdDaftarPengecekan[0]->id,
                'nama_komponen' => 'Tekanan Hidrolik',
                'standar' => 'Tekanan 5-7 bar',
                'frekuensi' => 'harian',
            ],
            [
                'mesin_id' => $createdDaftarPengecekan[0]->id,
                'nama_komponen' => 'Suhu Operasional',
                'standar' => 'Suhu maksimal 80°C',
                'frekuensi' => 'harian',
            ],
            // Untuk Pengecekan Bubut
            [
                'mesin_id' => $createdDaftarPengecekan[1]->id,
                'nama_komponen' => 'Kondisi Chuck',
                'standar' => 'Grip kuat, tidak ada slip, tidak ada keretakan',
                'frekuensi' => 'mingguan',
            ],
            // Untuk Pengecekan Las
            [
                'mesin_id' => $createdDaftarPengecekan[2]->id,
                'nama_komponen' => 'Gas Shielding',
                'standar' => 'Flow rate 15-20 L/min, tidak ada kebocoran',
                'frekuensi' => 'harian',
            ],
            // Untuk Pengecekan Gerinda
            [
                'mesin_id' => $createdDaftarPengecekan[3]->id,
                'nama_komponen' => 'Roda Gerinda',
                'standar' => 'Tidak ada keretakan, ketebalan minimal 30mm',
                'frekuensi' => 'harian',
            ],
        ];

        foreach ($komponenPengecekan as $komponen) {
            KomponenDaftarPengecekan::create($komponen);
            $this->command->info("  ✅ Created: {$komponen['nama_komponen']}");
        }

        // 5. CREATE 5 SPARE PARTS (SUKU CADANG)
        $this->command->info('🔩 Creating 5 Spare Parts...');
        
        // Pastikan ada kategori
        $category = SparePartCategory::first();
        if (!$category) {
            $category = SparePartCategory::create([
                'nama_kategori' => 'Komponen Mekanik',
                'deskripsi' => 'Komponen mekanik umum untuk mesin produksi',
            ]);
        }

        $spareParts = [
            [
                'kode_suku_cadang' => 'SP-001',
                'nama_suku_cadang' => 'Bearing 6205 2RS',
                'category_id' => $category->id,
                'deskripsi' => 'Deep groove ball bearing, sealed, inner diameter 25mm',
                'stok' => 25,
                'stok_minimum' => 10,
                'stok_maksimum' => 50,
                'lokasi_penyimpanan' => 'Rak A-12',
                'satuan' => 'pcs',
                'harga_satuan' => 45000,
                'supplier' => 'PT Bearing Indonesia',
                'status' => 'active',
                'part_number' => '6205-2RS',
                'manufacturer' => 'SKF',
            ],
            [
                'kode_suku_cadang' => 'SP-002',
                'nama_suku_cadang' => 'V-Belt Type A-40',
                'category_id' => $category->id,
                'deskripsi' => 'V-Belt tipe A dengan panjang 40 inch untuk transmisi daya',
                'stok' => 15,
                'stok_minimum' => 8,
                'stok_maksimum' => 30,
                'lokasi_penyimpanan' => 'Rak B-05',
                'satuan' => 'pcs',
                'harga_satuan' => 85000,
                'supplier' => 'CV Teknik Mandiri',
                'status' => 'active',
                'part_number' => 'A-40',
                'manufacturer' => 'Mitsuboshi',
            ],
            [
                'kode_suku_cadang' => 'SP-003',
                'nama_suku_cadang' => 'Hydraulic Oil ISO VG 46',
                'category_id' => $category->id,
                'deskripsi' => 'Oli hidrolik premium untuk sistem hidrolik industri',
                'stok' => 80,
                'stok_minimum' => 40,
                'stok_maksimum' => 150,
                'lokasi_penyimpanan' => 'Gudang Oli',
                'satuan' => 'liter',
                'harga_satuan' => 35000,
                'supplier' => 'PT Shell Indonesia',
                'status' => 'active',
                'part_number' => 'TELLUS-46',
                'manufacturer' => 'Shell',
            ],
            [
                'kode_suku_cadang' => 'SP-004',
                'nama_suku_cadang' => 'Filter Oli Hidrolik',
                'category_id' => $category->id,
                'deskripsi' => 'Filter oli hidrolik 10 micron untuk sistem hidrolik',
                'stok' => 5,
                'stok_minimum' => 5,
                'stok_maksimum' => 20,
                'lokasi_penyimpanan' => 'Rak C-08',
                'satuan' => 'pcs',
                'harga_satuan' => 125000,
                'supplier' => 'PT Filter Prima',
                'status' => 'active',
                'part_number' => 'HF-10M-150',
                'manufacturer' => 'Parker',
            ],
            [
                'kode_suku_cadang' => 'SP-005',
                'nama_suku_cadang' => 'Seal O-Ring NBR-70 (20x2mm)',
                'category_id' => $category->id,
                'deskripsi' => 'O-Ring NBR shore 70, diameter dalam 20mm, tebal 2mm',
                'stok' => 150,
                'stok_minimum' => 50,
                'stok_maksimum' => 300,
                'lokasi_penyimpanan' => 'Rak D-15',
                'satuan' => 'pcs',
                'harga_satuan' => 5500,
                'supplier' => 'CV Seal Teknik',
                'status' => 'active',
                'part_number' => 'OR-20x2-NBR70',
                'manufacturer' => 'NOK',
            ],
        ];

        foreach ($spareParts as $sparePartData) {
            $sparePart = SparePart::create($sparePartData);
            $this->command->info("  ✅ Created: {$sparePart->nama_suku_cadang}");
        }

        // Summary
        $this->command->newLine();
        $this->command->info('✨ Seeder completed successfully!');
        $this->command->info('📊 Summary:');
        $this->command->info("   - Mesin: " . Mesin::count());
        $this->command->info("   - Komponen Mesin: " . MComponent::count());
        $this->command->info("   - Daftar Pengecekan: " . DaftarPengecekan::count());
        $this->command->info("   - Komponen Daftar Pengecekan: " . KomponenDaftarPengecekan::count());
        $this->command->info("   - Spare Parts: " . SparePart::count());
    }
}
