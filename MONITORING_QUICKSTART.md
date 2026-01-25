# 🚀 Quick Start - Monitoring Pengecekan Mesin

## Akses Menu
1. Login ke aplikasi: `/admin`
2. Klik menu **"Monitoring Pengecekan"** di sidebar
3. URL langsung: `/admin/monitoring-pengecekans`

## Fitur Utama

### 📊 Widget Statistik (Header)
```
┌─────────────────────────────────────────────────────────┐
│ Total Mesin │ Sudah Dicek │ Sedang Dicek │ Belum Dicek │
│     10      │  5 (50%)    │      2       │      3      │
└─────────────────────────────────────────────────────────┘
```

### 📋 Tabel Monitoring
| Nama Mesin | Operator | Status | Waktu | Operator Pengecekan |
|------------|----------|--------|-------|---------------------|
| Mesin A | Budi | ✅ Sudah Dicek | 08:15 | Budi |
| Mesin B | Siti | ⏳ Sedang Dicek | 09:00 | Siti |
| Mesin C | Andi | ❌ Belum Dicek | - | - |

## Status Badge

| Badge | Arti | Warna | Icon |
|-------|------|-------|------|
| ✅ Sudah Dicek | Pengecekan selesai | Hijau | ✓ |
| ⏳ Sedang Dicek | Dalam proses | Kuning | 🕐 |
| ❌ Belum Dicek | Belum ada pengecekan | Merah | ✗ |

## Filter & Search

### Filter Status
```
Filter: [Pilih Status ▼]
        ├─ Sudah Dicek
        ├─ Sedang Dicek
        └─ Belum Dicek
```

### Search
```
🔍 [Cari mesin atau operator...]
```

## Auto-Refresh
- ⏱️ Otomatis refresh setiap **30 detik**
- 🔄 Update real-time tanpa reload manual

## Reset Harian
```
Hari Senin 08:00  →  Semua status "Belum Dicek"
         ↓
      Pengecekan
         ↓
Hari Senin 14:00  →  Semua "Sudah Dicek" (100%)
         ↓
      Hari Berganti
         ↓
Hari Selasa 07:00 →  RESET! Semua "Belum Dicek" lagi
```

## Use Case Harian

### ☀️ Pagi (Awal Shift)
- Buka menu monitoring
- Lihat semua mesin status "Belum Dicek"
- Pastikan operator mulai pengecekan

### 🌤️ Siang (Monitoring Progress)
- Pantau widget: berapa % sudah selesai?
- Cek mesin mana yang masih "Belum Dicek"
- Follow-up operator yang terlambat

### 🌙 Sore (Akhir Shift)
- Verifikasi semua mesin "Sudah Dicek"
- Pastikan 100% completion sebelum pulang

## Quick Troubleshooting

### Data tidak muncul?
```bash
php artisan cache:clear
php artisan config:clear
```

### Widget tidak tampil?
Widget sudah otomatis tampil di header halaman monitoring

### Tabel tidak refresh?
- Pastikan browser tidak dalam mode offline
- Check connection internet
- Refresh manual (F5) jika perlu

## Command Reference

```bash
# Clear cache
php artisan cache:clear

# Cek route tersedia
php artisan route:list --name=monitoring

# Test data (via tinker)
php artisan tinker
>>> App\Models\Mesin::with('operator')->get()
```

## Dokumentasi Lengkap

📄 **MONITORING_README.md** - Dokumentasi detail  
📄 **WIDGET_SETUP.md** - Setup widget  
📄 **MONITORING_SUMMARY.md** - Rangkuman lengkap  

---

**URL**: `/admin/monitoring-pengecekans`  
**Auto-Refresh**: 30 detik  
**Reset**: Setiap hari otomatis  
**Status**: ✅ Production Ready
