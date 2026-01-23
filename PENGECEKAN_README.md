# Fitur Pengecekan Mesin (Checklist Harian)

## Deskripsi
Fitur ini memungkinkan operator untuk melakukan pengecekan harian terhadap mesin yang menjadi tanggung jawabnya. Setiap mesin memiliki komponen-komponen yang harus diperiksa sesuai dengan parameter standar.

## Fitur Utama

### 1. **Pengecekan Harian Mesin**
- Operator dapat melihat daftar mesin yang menjadi tanggung jawabnya
- Sistem hanya menampilkan mesin yang **belum dicek hari ini**
- Setiap mesin hanya bisa dicek **sekali per hari**
- Pengecekan hanya bisa dilakukan pada **hari ini** (tidak bisa untuk kemarin atau besok)

### 2. **Checklist Komponen**
- Setiap komponen mesin memiliki:
  - Nama komponen
  - Standar yang harus dipenuhi
  - Status pengecekan: **Sesuai** atau **Tidak Sesuai**
- Jika status "Tidak Sesuai", operator **wajib** mengisi keterangan

### 3. **Kontrol Akses**
- Hanya operator yang **bertanggung jawab atas mesin** yang bisa melakukan pengecekan
- Divalidasi berdasarkan data `user_id` pada mesin

### 4. **Riwayat Pengecekan**
- Menampilkan daftar pengecekan yang sudah dilakukan
- Informasi yang ditampilkan:
  - Nama mesin
  - Tanggal pengecekan
  - Nama operator
  - Status pengecekan (Selesai/Dalam Proses)
  - Jumlah komponen yang diperiksa

## Struktur Database

### Tabel `pengecekan_mesins`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| mesin_id | bigint | Foreign key ke tabel mesins |
| user_id | bigint | Foreign key ke tabel users (operator) |
| tanggal_pengecekan | date | Tanggal dilakukan pengecekan |
| status | enum | selesai, dalam_proses |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Index/Constraint:**
- Unique constraint pada `mesin_id` dan `tanggal_pengecekan` (satu mesin hanya bisa dicek sekali per hari)

### Tabel `detail_pengecekan_mesins`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint | Primary key |
| pengecekan_mesin_id | bigint | Foreign key ke tabel pengecekan_mesins |
| komponen_mesin_id | bigint | Foreign key ke tabel komponen_mesins |
| status_sesuai | enum | sesuai, tidak_sesuai |
| keterangan | text | Wajib diisi jika status_sesuai = tidak_sesuai |
| created_at | timestamp | - |
| updated_at | timestamp | - |

## Model dan Relationships

### Model `PengecekanMesin`
```php
// Relationships
public function mesin(): BelongsTo
public function operator(): BelongsTo  
public function detailPengecekan(): HasMany
```

### Model `DetailPengecekanMesin`
```php
// Relationships
public function pengecekanMesin(): BelongsTo
public function komponenMesin(): BelongsTo
```

### Model `Mesin` (Updated)
```php
// Relationships
public function pengecekan(): HasMany
```

## Cara Penggunaan

### 1. Memulai Pengecekan
1. Login sebagai operator
2. Buka menu **Pengecekan Mesin**
3. Klik tombol **"Mulai Pengecekan"**
4. Pilih mesin yang akan diperiksa (hanya mesin yang belum dicek hari ini)
5. Sistem akan menampilkan semua komponen mesin beserta standarnya

### 2. Melakukan Checklist
1. Untuk setiap komponen, pilih:
   - **Sesuai**: Jika komponen memenuhi standar
   - **Tidak Sesuai**: Jika komponen tidak memenuhi standar
2. Jika memilih "Tidak Sesuai", **wajib** isi keterangan menjelaskan ketidaksesuaian
3. Klik **"Simpan Pengecekan"**

### 3. Melihat Riwayat
1. Buka menu **Pengecekan Mesin**
2. Tabel akan menampilkan semua pengecekan yang sudah dilakukan
3. Klik tombol **View** untuk melihat detail pengecekan

## Validasi Sistem

### Validasi di Form Pengecekan:
1. ✅ Mesin wajib dipilih
2. ✅ Hanya bisa pilih mesin yang operatornya sesuai dengan user yang login
3. ✅ Hanya bisa pilih mesin yang belum dicek hari ini
4. ✅ Status wajib dipilih untuk setiap komponen
5. ✅ Keterangan wajib diisi jika status "Tidak Sesuai"

### Validasi di Backend:
1. ✅ Cek apakah user adalah operator mesin tersebut
2. ✅ Cek apakah mesin sudah dicek hari ini (database constraint)
3. ✅ Validasi tanggal pengecekan (harus hari ini)

## Running Migration

```bash
php artisan migrate
```

## Seeding Data (Optional)

Untuk testing, Anda bisa menjalankan seeder yang sudah disediakan:

```bash
# Seed mesin dan komponen terlebih dahulu
php artisan db:seed --class=MesinSeeder

# Seed data pengecekan (5 hari terakhir untuk setiap mesin)
php artisan db:seed --class=PengecekanSeeder
```

## File-file yang Dibuat

### Migrations
- `2026_01_23_140854_create_pengecekan_mesins_table.php`
- `2026_01_23_140934_create_detail_pengecekan_mesins_table.php`

### Models
- `app/Models/PengecekanMesin.php`
- `app/Models/DetailPengecekanMesin.php`

### Filament Resources
- `app/Filament/Resources/PengecekanMesins/PengecekanMesinResource.php`
- `app/Filament/Resources/PengecekanMesins/Pages/ListPengecekanMesins.php`
- `app/Filament/Resources/PengecekanMesins/Pages/MulaiPengecekan.php`
- `app/Filament/Resources/PengecekanMesins/Pages/EditPengecekanMesin.php`
- `app/Filament/Resources/PengecekanMesins/Schemas/PengecekanMesinForm.php`
- `app/Filament/Resources/PengecekanMesins/Tables/PengecekanMesinsTable.php`

### Views
- `resources/views/filament/resources/pengecekan-mesins/pages/mulai-pengecekan.blade.php`

### Factories
- `database/factories/PengecekanMesinFactory.php`
- `database/factories/DetailPengecekanMesinFactory.php`

### Seeders
- `database/seeders/PengecekanSeeder.php`

## Catatan Penting

1. **Unique Constraint**: Database memiliki unique constraint pada `mesin_id` dan `tanggal_pengecekan`, jadi tidak mungkin ada duplikat pengecekan untuk mesin yang sama di hari yang sama.

2. **Auto-load Komponen**: Ketika operator memilih mesin, sistem otomatis menampilkan semua komponen mesin beserta standarnya.

3. **Read-Only View**: Halaman detail pengecekan adalah read-only, operator tidak bisa mengedit pengecekan yang sudah dilakukan.

4. **Status Badge**: Status pengecekan ditampilkan dengan badge berwarna:
   - Hijau (success) untuk "Selesai"
   - Kuning (warning) untuk "Dalam Proses"

## Screenshots / Flow

1. **Menu Pengecekan Mesin** → Tampilkan daftar pengecekan + tombol "Mulai Pengecekan"
2. **Form Pengecekan** → Pilih mesin → Checklist komponen
3. **Detail Pengecekan** → View read-only hasil pengecekan

## Troubleshooting

**Q: Mesin tidak muncul di dropdown saat mulai pengecekan?**
- A: Pastikan user yang login adalah operator dari mesin tersebut (cek field `user_id` di tabel `mesins`)
- A: Pastikan mesin belum dicek hari ini

**Q: Error saat menyimpan pengecekan?**
- A: Cek apakah semua komponen sudah diisi statusnya
- A: Jika ada status "Tidak Sesuai", pastikan keterangannya sudah diisi

**Q: Tidak bisa membuat pengecekan untuk mesin yang sama?**
- A: Setiap mesin hanya bisa dicek sekali per hari. Cek kembali apakah mesin sudah dicek hari ini.
