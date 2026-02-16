# System Cek - Monitoring, Maintenance, dan Manajemen Mesin

Sistem berbasis Laravel + Filament untuk mengelola:
- master mesin dan komponen,
- pengecekan harian mesin,
- workflow maintenance,
- stok dan transaksi suku cadang,
- audit trail dan pelaporan (PDF/Excel).

## Fitur Utama

- Manajemen master mesin lengkap (identitas, pengadaan, kondisi, dokumen).
- Manajemen komponen mesin dan jadwal penggantian.
- Pengecekan mesin harian oleh operator.
- Monitoring status pengecekan real-time.
- Maintenance request dan maintenance log.
- Sinkronisasi status mesin otomatis berdasarkan request aktif.
- Manajemen suku cadang dan transaksi stok (IN/OUT/RETURN/ADJUSTMENT).
- Audit dan repair konsistensi stok/transaksi suku cadang via Artisan command.
- Export laporan ke PDF dan Excel.
- Notifikasi in-app (database notifications).

## Teknologi

- PHP 8.2+
- Laravel 12
- Filament 4
- MySQL/MariaDB
- Tailwind CSS 4 + Vite
- Spatie Permission + Filament Shield
- Laravel Excel (Maatwebsite)
- DomPDF (barryvdh/laravel-dompdf)

## Arsitektur Modul

Resource utama Filament:
- `MesinResource`
- `DaftarPengecekanResource`
- `PengecekanMesins`
- `MaintenanceReports`
- `MRequests`
- `MLogs`
- `SpareParts`
- `SparePartTransactionResource`
- `Users`, `Roles`

Model/domain utama:
- Mesin: `mesins`
- Komponen: `m_components`
- Request maintenance: `m_requests`
- Log maintenance: `m_logs`
- Audit maintenance: `m_audits`
- Laporan maintenance: `maintenance_reports`
- Suku cadang: `spare_parts`
- Transaksi suku cadang: `spare_part_transactions`

## Persyaratan Sistem

- PHP 8.2 atau lebih baru
- Composer 2+
- Node.js 18+ dan npm
- MySQL/MariaDB aktif

## Instalasi Cepat

```bash
git clone <repo-url>
cd system_cek
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

Alternatif setup otomatis:

```bash
composer run setup
```

Jalankan development mode:

```bash
composer run dev
```

## Konfigurasi `.env`

Minimal yang wajib diisi:

```env
APP_NAME="System Cek"
APP_URL=http://localhost
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=system_cek
DB_USERNAME=root
DB_PASSWORD=
```

Disarankan untuk production:
- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Konfigurasikan `MAIL_*`
- Jalankan queue worker dan scheduler

## Akun Default Seeder

Setelah `php artisan migrate --seed`, akun default:

- Super Admin
  - Email: `admin@system-cek.com`
  - Password: `password`
- Supervisor
  - Email: `supervisor@system-cek.com`
  - Password: `password`

Penting: ubah password default sebelum dipakai di environment non-local.

## Role dan Akses

Role yang disediakan oleh `RoleSeeder`:
- `super_admin`
- `admin`
- `supervisor`
- `operator`
- `viewer`

Permission dikelola melalui Filament Shield.

## URL Aplikasi

- Root: `/` (redirect ke `/admin`)
- Panel admin Filament: `/admin`

## Workflow Kritis

### 1. Sinkronisasi status mesin vs request maintenance

Status master mesin otomatis:
- `maintenance` jika ada request aktif (`pending`, `approved`, `in_progress`).
- `aktif` jika tidak ada request aktif.

Sinkronisasi berjalan saat request/log maintenance berubah dan bisa dijalankan manual via command.

### 2. Stok suku cadang

- Penggunaan suku cadang dari maintenance dicatat sebagai transaksi `OUT`.
- Observer transaksi menjaga konsistensi nilai `stok_sebelum` dan `stok_sesudah`.
- Tersedia command audit/repair untuk koreksi data historis yang mismatch.

## Artisan Commands Penting

```bash
# Sinkronisasi status mesin berdasarkan request maintenance aktif
php artisan machine:sync-status
php artisan machine:sync-status --dry-run

# Audit konsistensi transaksi dan stok suku cadang
php artisan sparepart:audit-stock
php artisan sparepart:audit-stock --fix
php artisan sparepart:audit-stock --spare-part-id=1
php artisan sparepart:audit-stock --include-pending

# Cek komponen/mesin yang mendekati jadwal penggantian
php artisan machine:check-replacement
php artisan machine:check-replacement --days=60
```

## Scheduler

Saat ini jadwal yang sudah dikonfigurasi:
- `machine:sync-status` harian jam `00:10` (timezone `Asia/Jakarta`).

Agar scheduler berjalan, atur cron/task scheduler:

```bash
* * * * * cd /path/to/system_cek && php artisan schedule:run >> /dev/null 2>&1
```

## Queue Worker

Untuk notifikasi dan proses async lain:

```bash
php artisan queue:work
```

## Testing dan Quality Check

```bash
php artisan test
php artisan config:clear
php artisan optimize:clear
```

## Export Laporan

Sistem menyediakan export:
- Laporan pengecekan (PDF/Excel)
- Laporan maintenance (PDF/Excel)
- Master/detail mesin (PDF/Excel)
- Transaksi suku cadang (PDF)

Route export tersedia dalam middleware `auth`.

## Troubleshooting Cepat

- Perubahan kode tidak terbaca:
  - `php artisan optimize:clear`
- Halaman admin kosong/error asset:
  - `npm run build`
- Notifikasi tidak masuk:
  - pastikan queue worker berjalan
- Data stok terasa tidak konsisten:
  - jalankan `php artisan sparepart:audit-stock`
  - lanjutkan `--fix` jika ada mismatch

## Lisensi

Project ini mengikuti lisensi dari dependensi Laravel (MIT) kecuali ditentukan lain oleh organisasi pemilik source code.
