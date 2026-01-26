# Sistem Notifikasi

## Konsep Notifikasi

Sistem notifikasi telah diimplementasikan dengan konsep sebagai berikut:

### 1. **Admin & Manager**
Menerima notifikasi untuk:
- ✅ Ketika operator menemukan ketidaksesuaian
- ✅ Ketika teknisi selesai melakukan perbaikan

### 2. **Teknisi**
Menerima notifikasi untuk:
- ✅ Ketika operator menemukan ketidaksesuaian

### 3. **Operator**
Menerima notifikasi untuk:
- ✅ Ketika ketidaksesuaian sudah ditangani oleh teknisi

## Fitur Notifikasi

- ✅ **Badge Notifikasi**: Menampilkan jumlah notifikasi yang belum dibaca
- ✅ **Timestamp**: Setiap notifikasi memiliki waktu kejadian
- ✅ **Pesan Singkat**: Informasi ringkas tentang kejadian
- ✅ **Link Langsung**: Klik notifikasi untuk melihat detail maintenance report
- ✅ **Icon & Color**: Setiap notifikasi memiliki icon dan warna sesuai jenisnya
- ✅ **Real-time Polling**: Notifikasi di-refresh setiap 30 detik
- ✅ **Dropdown Style**: Notifikasi muncul dalam dropdown seperti user menu (bukan modal)
- ✅ **Smooth UI**: Hover effects dan transisi yang halus
- ✅ **Unread Indicator**: Border biru di sisi kiri untuk notifikasi yang belum dibaca

## Jenis Notifikasi

### 1. Ketidaksesuaian Ditemukan
- **Icon**: ⚠️ (warning triangle)
- **Color**: Warning (kuning/orange)
- **Trigger**: Ketika operator mencatat status "tidak_sesuai" pada pengecekan
- **Penerima**: Admin, Manager, dan Teknisi

### 2. Maintenance Selesai
- **Icon**: ✅ (check circle)
- **Color**: Success (hijau)
- **Trigger**: Ketika teknisi mengupload foto sesudah perbaikan
- **Penerima**: Admin, Manager, dan Operator yang melakukan pengecekan

## File yang Terlibat

### Notification Classes
- `app/Notifications/KetidaksesuaianDitemukanNotification.php`
- `app/Notifications/MaintenanceSelesaiNotification.php`

### Observer Classes
- `app/Observers/DetailPengecekanMesinObserver.php` - Mengirim notifikasi ketidaksesuaian
- `app/Observers/MaintenanceReportObserver.php` - Mengirim notifikasi maintenance selesai

### Configuration
- `app/Providers/Filament/AdminPanelProvider.php` - Konfigurasi database notifications dengan polling 30s

### Views (Custom)
- `resources/views/vendor/filament-notifications/database-notifications.blade.php` - Custom dropdown view untuk notifikasi

## Customization

### Dropdown Style
Notifikasi telah di-customize untuk menggunakan dropdown style (seperti user menu) alih-alih modal slide-over default Filament. Ini memberikan UX yang lebih familiar dan konsisten.

### Styling Features
- Header sticky dengan badge counter
- Max height 96 (max-h-96) dengan scroll untuk banyak notifikasi
- Highlight untuk notifikasi belum dibaca (background biru muda + border biru di kiri)
- Hover effect pada setiap notifikasi
- Empty state dengan icon dan pesan yang jelas
- Pagination support untuk banyak notifikasi

## Data Notifikasi

Setiap notifikasi menyimpan data berikut dalam format JSON:

```json
{
  "title": "Judul Notifikasi",
  "message": "Pesan singkat",
  "mesin": "Nama Mesin",
  "komponen": "Nama Komponen",
  "icon": "heroicon-o-...",
  "icon_color": "success|warning|danger",
  "url": "URL ke detail maintenance report",
  "maintenance_report_id": 123
}
```

## Cara Kerja

1. **Operator** melakukan pengecekan mesin dan menemukan ketidaksesuaian
2. **Observer** `DetailPengecekanMesinObserver` mendeteksi dan membuat `MaintenanceReport`
3. **Notifikasi** dikirim ke Admin, Manager, dan Teknisi
4. **Teknisi** melihat notifikasi dan melakukan perbaikan
5. Teknisi upload foto sebelum dan sesudah perbaikan
6. **Observer** `MaintenanceReportObserver` mendeteksi foto sesudah diupload
7. **Notifikasi** dikirim ke Admin, Manager, dan Operator

## Testing

Untuk test notifikasi, ikuti langkah berikut:

1. Login sebagai **Operator**
2. Buat pengecekan mesin baru
3. Set salah satu komponen dengan status "Tidak Sesuai"
4. Notifikasi akan dikirim ke Admin, Manager, dan Teknisi
5. Login sebagai **Teknisi**
6. Lihat notifikasi di icon bell (navbar)
7. Buka maintenance report dan upload foto sesudah
8. Login sebagai **Operator** yang tadi melakukan pengecekan
9. Lihat notifikasi bahwa perbaikan sudah selesai
