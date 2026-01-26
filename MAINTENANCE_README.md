# Sistem Laporan Maintenance

## Overview
Sistem Laporan Maintenance adalah fitur untuk mengelola proses maintenance mesin yang terintegrasi dengan sistem pengecekan mesin. Laporan maintenance akan dibuat secara otomatis ketika operator menemukan ketidaksesuaian pada komponen mesin.

## Fitur Utama

### 1. Auto-Generate Laporan Maintenance
- Laporan maintenance akan otomatis dibuat ketika ada ketidaksesuaian (`status_sesuai = false`) pada detail pengecekan mesin
- Sistem mencegah duplikasi laporan untuk issue yang sama

### 2. Workflow Status
Sistem menggunakan 3 status utama:

#### **Pending** (Menunggu Foto Awal)
- Status awal ketika laporan dibuat
- Teknisi harus upload foto kondisi sebelum maintenance
- Tidak bisa memulai proses maintenance sebelum upload foto awal

#### **In Progress** (Sedang Proses Maintenance)
- Status otomatis berubah setelah foto awal diupload
- Teknisi dapat:
  - Menambahkan catatan
  - Memilih suku cadang yang digunakan
  - Mengisi jumlah suku cadang

#### **Completed** (Selesai)
- Status otomatis berubah setelah foto akhir diupload
- Stok suku cadang otomatis berkurang sesuai jumlah yang digunakan
- Tanggal selesai tercatat otomatis

### 3. Manajemen Suku Cadang
- Master data suku cadang terpisah
- Tracking stok real-time
- **Constraint: Stok otomatis berkurang sesuai jumlah yang dipakai saat maintenance selesai**
- Alert stok menipis (merah: ≤10, kuning: ≤30, hijau: >30)
- Data yang disimpan: Kode, Nama, Deskripsi, Stok, Satuan

## Struktur Database

### Tabel: `maintenance_reports`
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| detail_pengecekan_mesin_id | bigint | FK ke detail_pengecekan_mesins |
| mesin_id | bigint | FK ke mesins |
| komponen_mesin_id | bigint | FK ke komponen_mesins |
| issue_description | text | Deskripsi masalah |
| status | enum | pending, in_progress, completed |
| foto_sebelum | string | Path foto kondisi awal |
| foto_sesudah | string | Path foto kondisi akhir |
| catatan_teknisi | text | Catatan dari teknisi |
| teknisi_id | bigint | FK ke users (teknisi) |
| tanggal_mulai | timestamp | Waktu mulai maintenance |
| tanggal_selesai | timestamp | Waktu selesai maintenance |

### Tabel: `spare_parts`
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| kode_suku_cadang | string | Kode unik suku cadang |
| nama_suku_cadang | string | Nama suku cadang |
| deskripsi | text | Deskripsi detail |
| stok | integer | Jumlah stok tersedia |
| satuan | string | Satuan (pcs, unit, liter, dll) |

### Tabel: `maintenance_report_spare_part` (Pivot)
| Field | Type | Description |
|-------|------|-------------|
| id | bigint | Primary key |
| maintenance_report_id | bigint | FK ke maintenance_reports |
| spare_part_id | bigint | FK ke spare_parts |
| jumlah_digunakan | integer | Jumlah yang digunakan |
| catatan | text | Catatan untuk suku cadang ini |

## Model Relations

### MaintenanceReport
- `belongsTo` DetailPengecekanMesin
- `belongsTo` Mesin
- `belongsTo` KomponenMesin
- `belongsTo` User (teknisi)
- `belongsToMany` SparePart (dengan pivot jumlah_digunakan, catatan)

### SparePart
- `belongsToMany` MaintenanceReport

### DetailPengecekanMesin
- `hasMany` MaintenanceReport

## Alur Penggunaan

### 1. Operator Melakukan Pengecekan
```
Operator → Pengecekan Mesin → Menemukan Ketidaksesuaian
                                        ↓
                              Set status_sesuai = false
                                        ↓
                        Auto-create MaintenanceReport (status: pending)
```

### 2. Teknisi Memulai Maintenance
```
Teknisi → Buka Laporan Maintenance (status: pending)
              ↓
        Upload Foto Kondisi Awal
              ↓
    Status otomatis berubah → In Progress
              ↓
        Tanggal mulai tercatat
```

### 3. Teknisi Melakukan Maintenance
```
Teknisi (status: in_progress) →
    • Tambahkan catatan proses maintenance
    • Pilih suku cadang yang digunakan
    • Input jumlah per suku cadang
    • Tambahkan catatan per suku cadang (opsional)
```

### 4. Teknisi Menyelesaikan Maintenance
```
Teknisi → Upload Foto Kondisi Akhir
              ↓
    Status otomatis berubah → Completed
              ↓
        Tanggal selesai tercatat
              ↓
    Stok suku cadang otomatis berkurang
```

## Permission & Access Control

### Create Laporan Manual
- **Hanya Teknisi** yang dapat membuat laporan maintenance manual
- Operator tidak dapat membuat manual, hanya otomatis dari sistem

### Edit & View
- Semua role dapat melihat laporan
- Teknisi dapat mengedit laporan yang belum completed
- Laporan dengan status completed tidak dapat diedit

## Fitur Khusus

### 1. Auto Status Update (Observer)
- Upload foto awal → Status: pending → in_progress
- Upload foto akhir → Status: in_progress → completed
- Auto set tanggal mulai & selesai

### 2. Stock Management
- Validasi stok tersedia saat memilih suku cadang
- Pengurangan stok otomatis saat maintenance completed
- Display stok tersedia real-time di form

### 3. Prevent Duplicate
- Sistem cek apakah sudah ada maintenance report aktif untuk detail pengecekan yang sama
- Hanya membuat laporan baru jika belum ada yang aktif

### 4. Visual Indicators
- Badge warna untuk status (pending: kuning, in_progress: biru, completed: hijau)
- Badge warna untuk stok (rendah: merah, sedang: kuning, cukup: hijau)
- Preview foto sebelum dan sesudah

## Navigation
Menu berada di grup **"Maintenance"**:
1. **Laporan Maintenance** (navigationSort: 1)
   - Ikon: Wrench Screwdriver
   - List, Create, Edit

2. **Suku Cadang** (navigationSort: 2)
   - Ikon: Cog
   - List, Create, Edit

## Filter & Search

### Laporan Maintenance
**Filters:**
- Status (pending, in_progress, completed)
- Mesin
- Teknisi

**Searchable:**
- ID
- Nama Mesin
- Nama Komponen
- Deskripsi Issue

### Suku Cadang
**Searchable:**
- Kode Suku Cadang
- Nama Suku Cadang

## Testing

### Seed Data
Jalankan seeder untuk membuat sample data suku cadang:
```bash
php artisan db:seed --class=SparePartSeeder
```

### Manual Testing Flow
1. Buat pengecekan mesin baru
2. Set komponen dengan status_sesuai = false
3. Cek apakah maintenance report otomatis dibuat
4. Login sebagai teknisi
5. Upload foto awal
6. Verifikasi status berubah ke in_progress
7. Tambahkan suku cadang
8. Upload foto akhir
9. Verifikasi status berubah ke completed
10. Cek stok suku cadang berkurang

## Files Created

### Models
- `app/Models/MaintenanceReport.php`
- `app/Models/SparePart.php`

### Migrations
- `database/migrations/2026_01_26_194442_create_maintenance_reports_table.php`
- `database/migrations/2026_01_26_194453_create_spare_parts_table.php`
- `database/migrations/2026_01_26_194501_create_maintenance_report_spare_part_table.php`

### Factories
- `database/factories/MaintenanceReportFactory.php`
- `database/factories/SparePartFactory.php`

### Seeders
- `database/seeders/SparePartSeeder.php`

### Observers
- `app/Observers/MaintenanceReportObserver.php`
- `app/Observers/DetailPengecekanMesinObserver.php`

### Filament Resources
- `app/Filament/Resources/MaintenanceReports/MaintenanceReportResource.php`
- `app/Filament/Resources/MaintenanceReports/Schemas/MaintenanceReportForm.php`
- `app/Filament/Resources/MaintenanceReports/Tables/MaintenanceReportsTable.php`
- `app/Filament/Resources/MaintenanceReports/Pages/`
  - `CreateMaintenanceReport.php`
  - `EditMaintenanceReport.php`
  - `ListMaintenanceReports.php`
- `app/Filament/Resources/SpareParts/SparePartResource.php`
- `app/Filament/Resources/SpareParts/Schemas/SparePartForm.php`
- `app/Filament/Resources/SpareParts/Tables/SparePartsTable.php`
- `app/Filament/Resources/SpareParts/Pages/`
  - `CreateSparePart.php`
  - `EditSparePart.php`
  - `ListSpareParts.php`

## Future Enhancements
1. Notifikasi email/WhatsApp ke teknisi saat ada laporan baru
2. Dashboard widget untuk monitoring maintenance
3. Export laporan maintenance ke PDF/Excel
4. History tracking untuk setiap perubahan
5. SLA (Service Level Agreement) tracking
6. Preventive maintenance scheduling
