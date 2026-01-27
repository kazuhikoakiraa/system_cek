# Update Frekuensi Pengecekan Komponen Mesin

## Ringkasan Perubahan

Sistem pengecekan mesin telah diperbarui untuk menerapkan logika frekuensi pengecekan yang benar. Sekarang komponen dengan frekuensi **mingguan** dan **bulanan** hanya bisa dicek sesuai dengan jadwalnya, bukan setiap hari.

## Perubahan Utama

### 1. Model KomponenMesin

**File:** `app/Models/KomponenMesin.php`

Ditambahkan method-method helper:

- **`isCheckable()`**: Mengecek apakah komponen sudah waktunya untuk dicek berdasarkan frekuensi
  - Harian: Selalu bisa dicek
  - Mingguan: Bisa dicek jika sudah lewat 7 hari dari pengecekan terakhir
  - Bulanan: Bisa dicek jika sudah lewat 30 hari dari pengecekan terakhir

- **`getLastCheck()`**: Mendapatkan detail pengecekan terakhir untuk komponen

- **`getNextCheckDate()`**: Menghitung tanggal pengecekan berikutnya berdasarkan frekuensi

### 2. Halaman Mulai Pengecekan

**File:** `app/Filament/Resources/PengecekanMesins/Pages/MulaiPengecekan.php`

#### Perubahan pada Form

- Form sekarang menampilkan informasi tambahan untuk setiap komponen:
  - Status checkable/tidak checkable
  - Hasil pengecekan terakhir (jika ada)
  - Tanggal pengecekan terakhir
  - Tanggal pengecekan berikutnya (untuk komponen yang belum waktunya)

#### Logika Input Field

- **Komponen yang bisa dicek**: Menampilkan radio button dan textarea untuk input status dan keterangan
- **Komponen yang belum waktunya**: Menampilkan hasil pengecekan terakhir saja, tidak bisa diisi

#### Validasi Penyimpanan

- Hanya komponen yang `is_checkable = true` dan memiliki status yang disimpan
- Notifikasi menampilkan jumlah komponen yang berhasil dicek
- Validasi tambahan untuk memastikan ada minimal 1 komponen yang bisa dicek

### 3. Model Mesin

**File:** `app/Models/Mesin.php`

- Ditambahkan alias relasi `komponenMesin()` untuk konsistensi dengan query

## Cara Kerja Sistem Baru

### Skenario 1: Komponen Harian

```
Komponen: Kebersihan Area
Frekuensi: Harian
Status: Selalu bisa dicek setiap hari
```

### Skenario 2: Komponen Mingguan

```
Komponen: Pelumasan Bearing
Frekuensi: Mingguan
Pengecekan Terakhir: 20 Januari 2026

Status hari ini (27 Januari 2026):
✅ Bisa dicek (sudah lewat 7 hari)
Input: Aktif
```

```
Komponen: Pelumasan Bearing
Frekuensi: Mingguan
Pengecekan Terakhir: 25 Januari 2026

Status hari ini (27 Januari 2026):
❌ Belum waktunya (baru 2 hari)
Tampilan: Menampilkan hasil pengecekan 25 Januari
Input: Disabled
Pengecekan berikutnya: 1 Februari 2026
```

### Skenario 3: Komponen Bulanan

```
Komponen: Kalibrasi Sensor
Frekuensi: Bulanan
Pengecekan Terakhir: 15 Desember 2025

Status hari ini (27 Januari 2026):
✅ Bisa dicek (sudah lewat 30 hari)
Input: Aktif
```

## Tampilan UI

### Komponen yang Bisa Dicek

```
┌─────────────────────────────────────────────┐
│ Komponen: Kebersihan Area                   │
│ Standar: Tidak ada kotoran                  │
│ Frekuensi: Harian                           │
│                                             │
│ Status: ○ Sesuai  ○ Tidak Sesuai          │
└─────────────────────────────────────────────┘
```

### Komponen yang Belum Waktunya

```
┌─────────────────────────────────────────────┐
│ Komponen: Pelumasan Bearing                 │
│ Standar: Bearing bergerak lancar            │
│ Frekuensi: Mingguan                         │
│            Berikutnya: 01/02/2026           │
│                                             │
│ Hasil Pengecekan Terakhir:                  │
│ ┌─────────────────────────────────────────┐ │
│ │ [Sesuai]                                │ │
│ │ Dicek: 25/01/2026 08:30                 │ │
│ └─────────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

## Testing

### Test Case 1: Pengecekan Komponen Harian

1. Pilih mesin dengan komponen harian
2. Pastikan komponen harian selalu muncul dan bisa diisi
3. Simpan pengecekan
4. Besok, komponen harian harus muncul lagi dan bisa diisi

### Test Case 2: Pengecekan Komponen Mingguan

1. Cek komponen mingguan hari ini (Senin)
2. Besok (Selasa), komponen mingguan harus:
   - Muncul di list
   - Menampilkan hasil pengecekan Senin
   - Input disabled
   - Menampilkan tanggal pengecekan berikutnya
3. Minggu depan (Senin), komponen mingguan harus bisa dicek lagi

### Test Case 3: Pengecekan Komponen Bulanan

1. Cek komponen bulanan hari ini
2. Selama 29 hari ke depan, komponen harus menampilkan hasil terakhir
3. Hari ke-30, komponen harus bisa dicek lagi

### Test Case 4: Mesin dengan Mixed Frekuensi

```
Mesin A memiliki:
- 3 komponen harian
- 2 komponen mingguan (sudah dicek 3 hari lalu)
- 1 komponen bulanan (sudah dicek 15 hari lalu)

Hasil:
✅ 3 komponen harian bisa dicek
❌ 2 komponen mingguan tampil hasil terakhir (disabled)
❌ 1 komponen bulanan tampil hasil terakhir (disabled)
📝 Total yang disimpan: 3 komponen
```

## Notifikasi

### Berhasil Simpan

```
✅ Berhasil
Pengecekan mesin berhasil disimpan. 3 komponen dicek.
```

### Tidak Ada Komponen yang Bisa Dicek

```
⚠️ Tidak Ada Komponen
Tidak ada komponen yang perlu dicek hari ini berdasarkan frekuensi masing-masing.
```

### Belum Waktunya

```
ℹ️ Belum Waktunya
Semua komponen belum mencapai waktu pengecekan berikutnya.
```

## Database Schema

Tidak ada perubahan pada struktur database. Sistem menggunakan:

- `komponen_mesins.frekuensi` (enum: 'harian', 'mingguan', 'bulanan')
- `detail_pengecekan_mesins.created_at` untuk menghitung interval
- Relasi existing antara tabel

## Troubleshooting

### Komponen Mingguan Tidak Muncul di Dropdown Mesin

**Penyebab**: Query filter mesin hanya menampilkan mesin yang memiliki minimal 1 komponen harian atau belum pernah dicek.

**Solusi**: Jika semua komponen sudah dicek dan belum waktunya, mesin tidak akan muncul di dropdown. Ini adalah behavior yang diharapkan.

### Komponen Masih Bisa Dicek Padahal Belum 7 Hari

**Penyebab**: Perhitungan `diffInDays()` menggunakan timestamp pengecekan, bukan tanggal.

**Solusi**: Sudah ditangani di method `isCheckable()` dengan menggunakan `diffInDays()` yang akurat.

### Hasil Pengecekan Terakhir Tidak Muncul

**Penyebab**: Relasi atau eager loading bermasalah.

**Solusi**: 
```php
$lastCheck = $komponen->detailPengecekan()
    ->with('pengecekanMesin')
    ->whereHas('pengecekanMesin')
    ->latest('created_at')
    ->first();
```

## Future Improvements

1. **Notifikasi Reminder**: Kirim notifikasi H-1 sebelum komponen mingguan/bulanan perlu dicek
2. **Kalender Pengecekan**: Tampilan kalender untuk melihat jadwal pengecekan
3. **Laporan Frekuensi**: Dashboard untuk melihat compliance terhadap jadwal pengecekan
4. **Custom Interval**: Fleksibilitas untuk set interval custom (misal: setiap 3 hari, 2 minggu, dll)

## Changelog

### Version 2.0 - 27 Januari 2026

- ✅ Implementasi validasi frekuensi pengecekan
- ✅ Tampilan hasil pengecekan terakhir untuk komponen yang belum waktunya
- ✅ Notifikasi jumlah komponen yang dicek
- ✅ Filter mesin berdasarkan ketersediaan komponen yang bisa dicek
