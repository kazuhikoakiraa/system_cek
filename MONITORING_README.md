# Monitoring Pengecekan Mesin

## Deskripsi
Menu Monitoring Pengecekan Mesin adalah fitur yang menampilkan status real-time pengecekan mesin untuk hari ini. Menu ini memudahkan supervisor atau manager untuk memantau:
- Mesin mana saja yang sudah dicek
- Mesin mana yang sedang dalam proses pengecekan
- Mesin mana yang belum dicek sama sekali

## Fitur Utama

### 1. Monitoring Real-time
- **Auto-refresh setiap 30 detik**: Tabel akan otomatis refresh untuk menampilkan status terkini
- **Status Reset Otomatis**: Setiap hari status akan reset, sehingga hanya menampilkan status pengecekan hari ini

### 2. Informasi yang Ditampilkan
Untuk setiap mesin, tabel menampilkan:
- **Nama Mesin**: Identitas mesin yang harus dicek
- **Operator Bertanggung Jawab**: Operator yang ditunjuk untuk mengoperasikan mesin tersebut
- **Status Pengecekan Hari Ini**: 
  - 🔴 **Belum Dicek**: Mesin belum dilakukan pengecekan hari ini
  - 🟡 **Sedang Dicek**: Pengecekan sedang dalam proses
  - 🟢 **Sudah Dicek**: Pengecekan sudah selesai dilakukan
- **Waktu Pengecekan**: Jam berapa pengecekan dilakukan (jika sudah/sedang dicek)
- **Operator Pengecekan**: Siapa yang melakukan pengecekan (jika sudah/sedang dicek)

### 3. Fitur Filter dan Search
- **Filter berdasarkan Status**: Tampilkan hanya mesin dengan status tertentu
- **Search**: Cari mesin berdasarkan nama mesin atau nama operator
- **Sort**: Urutkan berdasarkan nama mesin, operator, atau status

### 4. Widget Statistik (Dashboard)
Widget `StatusPengecekanOverview` menampilkan:
- **Total Mesin**: Jumlah total mesin dalam sistem
- **Sudah Dicek**: Jumlah dan persentase mesin yang sudah dicek hari ini
- **Sedang Dicek**: Jumlah mesin yang sedang dalam proses pengecekan
- **Belum Dicek**: Jumlah mesin yang belum dicek dan memerlukan perhatian

## Cara Menggunakan

### Akses Menu
1. Login ke aplikasi
2. Klik menu **"Monitoring Pengecekan"** di sidebar
3. Tabel akan menampilkan semua mesin dengan status pengecekan hari ini

### Melihat Detail Status
- Badge berwarna menunjukkan status:
  - **Merah** dengan icon ❌: Belum dicek
  - **Kuning** dengan icon 🕐: Sedang dicek
  - **Hijau** dengan icon ✅: Sudah dicek

### Filter Data
1. Klik tombol filter di bagian atas tabel
2. Pilih status yang ingin ditampilkan:
   - Sudah Dicek
   - Sedang Dicek
   - Belum Dicek
3. Klik "Apply" untuk menerapkan filter

### Search/Pencarian
- Ketik nama mesin atau nama operator di kolom pencarian
- Hasil akan otomatis difilter sesuai kata kunci

## Logika Reset Harian

Status pengecekan **otomatis reset setiap hari**:
- Sistem hanya membaca data pengecekan dengan `tanggal_pengecekan = hari ini`
- Jika tidak ada data pengecekan untuk hari ini, status akan tampil "Belum Dicek"
- Data pengecekan hari-hari sebelumnya tetap tersimpan di database untuk keperluan history/laporan

Contoh:
```
Hari Senin:
- Mesin A: Sudah Dicek (jam 08:00)
- Mesin B: Belum Dicek

Hari Selasa (hari berikutnya):
- Mesin A: Belum Dicek (reset otomatis)
- Mesin B: Belum Dicek (reset otomatis)
```

## File yang Dibuat

### 1. Resource
**File**: `app/Filament/Resources/MonitoringPengecekanResource.php`
- Definisi resource untuk monitoring pengecekan
- Konfigurasi tabel dengan kolom dan filter
- Query untuk menampilkan status hari ini
- Auto-refresh setiap 30 detik

### 2. Page
**File**: `app/Filament/Resources/MonitoringPengecekanResource/Pages/ListMonitoringPengecekan.php`
- Halaman list untuk menampilkan tabel monitoring
- Kustomisasi title dan heading dengan tanggal hari ini

### 3. Widget (Optional)
**File**: `app/Filament/Widgets/StatusPengecekanOverview.php`
- Widget statistik untuk dashboard
- Menampilkan ringkasan status pengecekan hari ini
- Dapat ditambahkan ke dashboard atau halaman monitoring

## Menambahkan Widget ke Dashboard

Untuk menambahkan widget ke dashboard, edit file panel Filament:
```php
// app/Providers/Filament/AdminPanelProvider.php

use App\Filament\Widgets\StatusPengecekanOverview;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... konfigurasi lain
        ->widgets([
            StatusPengecekanOverview::class,
            // widget lainnya...
        ]);
}
```

## Database Schema

Menu ini menggunakan tabel yang sudah ada:

### Tabel: `mesins`
- `id`: ID mesin
- `nama_mesin`: Nama mesin
- `user_id`: ID operator yang bertanggung jawab (foreign key ke `users`)

### Tabel: `pengecekan_mesins`
- `id`: ID pengecekan
- `mesin_id`: ID mesin yang dicek (foreign key ke `mesins`)
- `user_id`: ID operator yang melakukan pengecekan (foreign key ke `users`)
- `tanggal_pengecekan`: Tanggal pengecekan (DATE)
- `status`: Status pengecekan ('selesai' atau 'dalam_proses')

**Unique Constraint**: (`mesin_id`, `tanggal_pengecekan`) - Memastikan satu mesin hanya bisa dicek sekali per hari.

## Troubleshooting

### Tabel tidak menampilkan data
- Pastikan ada data mesin di database
- Cek apakah ada data pengecekan untuk hari ini
- Periksa relasi `operator()` pada model Mesin

### Status tidak update otomatis
- Pastikan auto-refresh (poll) aktif: `->poll('30s')`
- Clear cache dengan: `php artisan cache:clear`

### Widget tidak muncul
- Pastikan widget sudah didaftarkan di panel provider
- Clear cache: `php artisan filament:cache-components`

## Maintenance

### Membersihkan Data Lama
Jika ingin membersihkan data pengecekan yang sudah lama (opsional):
```php
// Hapus data pengecekan lebih dari 30 hari
\App\Models\PengecekanMesin::where('tanggal_pengecekan', '<', now()->subDays(30))->delete();
```

**Note**: Sebaiknya buat command atau scheduled job untuk ini.

## Tips Penggunaan

1. **Gunakan di awal shift**: Buka menu ini di awal shift untuk mengetahui mesin mana yang perlu dicek
2. **Monitor progress**: Pantau secara berkala untuk memastikan semua mesin sudah dicek
3. **Identifikasi bottleneck**: Jika ada mesin yang sering terlambat dicek, investigasi penyebabnya
4. **Kombinasi dengan dashboard**: Tempatkan widget di dashboard untuk overview cepat

## Developer Notes

### Optimasi Query
Resource ini menggunakan eager loading untuk menghindari N+1 query:
```php
// Query otomatis include relasi operator
$query->with('operator')
```

### Custom State Column
Kolom status menggunakan `state()` callback untuk menghitung status secara dinamis berdasarkan data pengecekan hari ini:
```php
->state(function (Mesin $record): string {
    $pengecekanHariIni = $record->pengecekan()
        ->whereDate('tanggal_pengecekan', today())
        ->first();
    // ... logic
})
```

### Performance
- Auto-refresh setiap 30 detik
- Query dioptimasi dengan `whereDate()` dan index database
- Pagination otomatis untuk dataset besar
