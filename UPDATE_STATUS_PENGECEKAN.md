# Update Status Pengecekan Mesin - Penggabungan Status

## Tanggal Update: 27 Januari 2026

## Perubahan yang Dilakukan

### Ringkasan
Status pada laporan pengecekan mesin telah diubah dari **4 status** menjadi **3 status** dengan menggabungkan status "Tidak Dicek" dengan "Tidak Ada Data Pengecekan".

### Status Sebelumnya (4 Status)
1. ✅ **Sudah Dicek** - Pengecekan telah selesai dilakukan
2. 🕐 **Sedang Dicek** - Pengecekan sedang dalam proses
3. ❌ **Tidak Dicek** - Mesin tidak dilakukan pengecekan (ada record di database dengan status 'tidak_dicek')
4. ⚪ **Belum Ada Data** - Tidak ada record pengecekan di database

### Status Sekarang (3 Status)
1. ✅ **Sudah Dicek** - Pengecekan telah selesai dilakukan (status = 'selesai')
2. 🕐 **Sedang Dicek** - Pengecekan sedang dalam proses (status = 'dalam_proses')
3. ⚪ **Tidak Ada Data Pengecekan/Tidak Dicek** - Tidak ada record pengecekan di database (tidak ada record = tidak dicek)

## Alasan Perubahan

Status "Tidak Dicek" dan "Belum Ada Data" pada dasarnya memiliki arti yang sama:
- Mesin tidak dilakukan pengecekan pada hari tersebut
- Tidak ada data pengecekan yang tersimpan

Menggabungkan kedua status ini membuat sistem lebih sederhana dan logis:
- **Jika ada data pengecekan**: status bisa "Sudah Dicek" atau "Sedang Dicek"
- **Jika tidak ada data pengecekan**: berarti mesin tidak dicek (label: "Tidak Ada Data Pengecekan/Tidak Dicek")

## File-File yang Diubah

### 1. Migration
**File**: `database/migrations/2026_01_27_000000_merge_tidak_dicek_status.php`
- Menghapus semua record dengan status 'tidak_dicek'
- Mengubah enum status di tabel `pengecekan_mesins` dari 3 value menjadi 2 value
- Mengubah enum status_sesuai di tabel `detail_pengecekan_mesins` dari 3 value menjadi 2 value

### 2. Table Resource
**File**: `app/Filament/Resources/PengecekanMesins/Tables/PengecekanMesinsTable.php`
- Mengubah label status dari "Belum Ada Data" menjadi "Tidak Ada Data Pengecekan/Tidak Dicek"
- Menghapus filter dan query untuk status 'tidak_dicek'
- Menyederhanakan filter menjadi 3 opsi saja

### 3. Widgets
**File**: `app/Filament/Widgets/StatusPengecekanOverview.php`
- Mengubah label stat dari "Belum Dicek" menjadi "Tidak Ada Data/Tidak Dicek"
- Mengubah icon dari 'x-circle' (danger/red) menjadi 'minus-circle' (gray)
- Mengubah description dari "Perlu dicek hari ini" menjadi "Belum ada pengecekan"

**File**: `app/Filament/Widgets/LaporanPengecekanSummary.php`
- Menghapus stat "Tidak Dicek (N/A)"
- Mengubah jumlah kolom dari 5 menjadi 4

### 4. Commands
**File**: `app/Console/Commands/GenerateDailyPengecekan.php`
- Command diubah untuk hanya menampilkan peringatan bahwa command tidak diperlukan lagi
- Tidak lagi membuat record pengecekan dengan status 'tidak_dicek'

**File**: `app/Console/Commands/BackfillPengecekan.php`
- Command diubah untuk hanya menampilkan peringatan bahwa command tidak diperlukan lagi
- Tidak lagi membuat record historis dengan status 'tidak_dicek'

### 5. Pages
**File**: `app/Filament/Pages/LaporanPengecekan.php`
- Menghapus penghitungan `total_tidak_dicek`
- Mengubah return value getSummaryData() untuk tidak lagi menyertakan 'total_tidak_dicek'

### 6. Exports
**File**: `app/Exports/LaporanPengecekanExcel.php`
- Menghapus simbol '○' untuk status 'tidak_dicek'
- Mengubah keterangan dari 2 baris menjadi 1 baris:
  - Sebelum: "○ = Tidak Dicek" dan "- = Tidak ada data"
  - Sekarang: "- = Tidak ada data pengecekan/tidak dicek"

### 7. Views
**File**: `resources/views/exports/laporan-pengecekan-pdf.blade.php`
- Menghapus kondisi untuk menampilkan '○' pada status 'tidak_dicek'
- Mengubah keterangan legend
- Menghapus class 'check-skip'

**File**: `resources/views/filament/pages/laporan-pengecekan.blade.php`
- Menghapus variabel `$tidakDicek`
- Menghapus kondisi untuk menghitung status 'tidak_dicek'
- Menghapus keterangan "N/A = Tidak Dicek"

### 8. Scheduler
**File**: `routes/console.php`
- Menonaktifkan schedule untuk command `pengecekan:generate-daily`
- Menambahkan komentar bahwa schedule tidak diperlukan lagi

## Cara Menjalankan Update

### 1. Backup Database
```bash
# Backup database sebelum migration
mysqldump -u username -p database_name > backup_before_merge_status.sql
```

### 2. Jalankan Migration
```bash
php artisan migrate
```

Migration akan:
- Menghapus semua record pengecekan dengan status 'tidak_dicek'
- Menghapus semua detail pengecekan dengan status_sesuai 'tidak_dicek'
- Mengubah enum di database untuk menghapus opsi 'tidak_dicek'

### 3. Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan filament:cache-components
```

### 4. Restart Queue (jika menggunakan queue worker)
```bash
php artisan queue:restart
```

## Dampak pada Sistem

### ✅ Positif
- **Lebih sederhana**: Mengurangi kompleksitas dengan menghapus status yang redundan
- **Lebih jelas**: Status "tidak ada data" dan "tidak dicek" memiliki makna yang sama
- **Performa lebih baik**: Tidak perlu lagi membuat record dummy untuk mesin yang tidak dicek
- **Database lebih ringan**: Tidak menyimpan record yang tidak perlu

### ⚠️ Yang Perlu Diperhatikan
- **Data historis**: Record dengan status 'tidak_dicek' akan dihapus dari database
- **Laporan lama**: Laporan yang sudah di-generate sebelumnya masih menggunakan status lama
- **Command scheduler**: Schedule untuk generate-daily tidak diperlukan lagi dan sudah dinonaktifkan

## Testing Checklist

- [ ] Menu Monitoring Pengecekan menampilkan 3 status dengan benar
- [ ] Widget statistik menampilkan 4 stat (bukan 5)
- [ ] Filter status hanya memiliki 3 opsi
- [ ] Export PDF tidak menampilkan simbol '○'
- [ ] Export Excel memiliki keterangan yang benar
- [ ] Laporan pengecekan tidak menghitung "tidak dicek"
- [ ] Command generate-daily menampilkan peringatan
- [ ] Command backfill menampilkan peringatan

## Rollback

Jika diperlukan rollback:

```bash
# 1. Rollback migration
php artisan migrate:rollback

# 2. Restore database dari backup
mysql -u username -p database_name < backup_before_merge_status.sql

# 3. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Pertanyaan & Jawaban

**Q: Bagaimana dengan data pengecekan yang lama dengan status 'tidak_dicek'?**  
A: Data tersebut akan dihapus oleh migration. Jika Anda ingin menyimpannya untuk arsip, lakukan backup database sebelum migration.

**Q: Apakah command generate-daily masih berfungsi?**  
A: Command masih ada tapi sudah dimodifikasi untuk hanya menampilkan peringatan. Schedule-nya sudah dinonaktifkan.

**Q: Bagaimana cara mengetahui mesin mana yang tidak dicek?**  
A: Mesin yang tidak dicek akan ditampilkan dengan status "Tidak Ada Data Pengecekan/Tidak Dicek" (badge abu-abu dengan icon minus-circle) di menu Monitoring Pengecekan.

**Q: Apakah ini mempengaruhi cara operator melakukan pengecekan?**  
A: Tidak. Cara operator melakukan pengecekan tetap sama. Perubahan ini hanya pada cara sistem menampilkan status mesin yang tidak dicek.

## Kontak

Jika ada pertanyaan atau masalah terkait update ini, silakan hubungi tim development.
