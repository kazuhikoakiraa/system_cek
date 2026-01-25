# ❓ FAQ & Troubleshooting - Monitoring Pengecekan Mesin

## 📋 Frequently Asked Questions (FAQ)

### Q1: Bagaimana cara mengakses menu Monitoring Pengecekan?
**A:** 
1. Login ke aplikasi di `/admin`
2. Lihat sidebar kiri, klik menu **"Monitoring Pengecekan"**
3. Atau akses langsung: `/admin/monitoring-pengecekans`

---

### Q2: Kenapa status mesin saya masih "Belum Dicek" padahal sudah melakukan pengecekan?
**A:** Kemungkinan penyebabnya:
- ✅ Pengecekan belum disimpan/complete
- ✅ Status pengecekan masih "dalam_proses", bukan "selesai"
- ✅ Pengecekan dilakukan untuk tanggal lain (bukan hari ini)

**Solusi:**
```bash
# Cek data pengecekan
php artisan tinker
>>> $mesin = App\Models\Mesin::where('nama_mesin', 'Nama Mesin Anda')->first()
>>> $pengecekan = $mesin->pengecekan()->whereDate('tanggal_pengecekan', today())->first()
>>> echo $pengecekan ? $pengecekan->status : 'Tidak ada pengecekan'
```

---

### Q3: Apakah status akan reset otomatis setiap hari?
**A:** Ya! Status pengecekan otomatis reset setiap hari:
- Sistem hanya membaca data pengecekan dengan tanggal = hari ini
- Jika tidak ada data untuk hari ini, status tampil "Belum Dicek"
- Data historis tetap tersimpan untuk laporan

---

### Q4: Berapa lama data pengecekan disimpan?
**A:** 
- **Semua data pengecekan disimpan permanent** di database
- Tidak ada automatic deletion
- Jika ingin cleanup data lama, harus dilakukan manual

---

### Q5: Apakah satu mesin bisa dicek lebih dari sekali per hari?
**A:** Tidak. Database memiliki **unique constraint** pada (`mesin_id`, `tanggal_pengecekan`):
- Satu mesin hanya bisa dicek **1 kali per hari**
- Jika coba input ulang, akan error duplicate entry
- Operator harus menyelesaikan pengecekan dalam satu sesi

---

### Q6: Bagaimana cara menambahkan operator baru ke mesin?
**A:** 
Edit data mesin di menu **"Mesin"**:
1. Klik menu "Mesin"
2. Edit mesin yang ingin diubah operatornya
3. Pilih operator baru dari dropdown "Operator Bertanggung Jawab"
4. Save

---

### Q7: Widget statistik tidak update?
**A:** Widget update otomatis setiap 30 detik bersama tabel. Jika tidak update:
```bash
# Clear cache
php artisan cache:clear
php artisan view:clear

# Refresh browser (Ctrl+F5 / Cmd+Shift+R)
```

---

### Q8: Apakah bisa export data monitoring ke Excel?
**A:** Fitur export belum tersedia di versi ini. Untuk sementara:
- Screenshot tabel
- Copy-paste manual
- Atau tambahkan fitur export (enhancement di masa depan)

---

### Q9: Bagaimana cara melihat history pengecekan minggu lalu?
**A:** Menu Monitoring hanya menampilkan data hari ini. Untuk history:
- Gunakan menu **"Pengecekan Mesin"** untuk melihat semua riwayat
- Filter berdasarkan tanggal yang diinginkan

---

### Q10: Apakah auto-refresh bisa dimatikan?
**A:** Ya, edit file Resource:
```php
// File: app/Filament/Resources/MonitoringPengecekanResource.php

public static function table(Table $table): Table
{
    return $table
        // ... columns, filters, etc ...
        // ->poll('30s');  // Comment line ini untuk disable auto-refresh
}
```

---

## 🔧 Troubleshooting

### Problem 1: Menu tidak muncul di sidebar
**Symptoms:** Menu "Monitoring Pengecekan" tidak terlihat di sidebar

**Possible Causes:**
- Cache belum di-clear
- Permission/role tidak sesuai
- Resource tidak terdaftar

**Solutions:**
```bash
# 1. Clear semua cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 2. Cek apakah resource terdaftar
php artisan route:list --name=monitoring

# 3. Restart server
php artisan serve
```

---

### Problem 2: Error "Class not found"
**Symptoms:** 
```
Class 'App\Filament\Resources\MonitoringPengecekanResource' not found
```

**Solutions:**
```bash
# Rebuild autoload
composer dump-autoload

# Clear optimization
php artisan optimize:clear

# Restart server
```

---

### Problem 3: Tabel kosong / tidak ada data
**Symptoms:** Tabel monitoring tidak menampilkan mesin apapun

**Diagnosis:**
```bash
php artisan tinker
>>> App\Models\Mesin::count()  # Cek apakah ada data mesin
```

**Solutions:**
- Pastikan ada data mesin di database
- Cek relasi `operator()` sudah benar
- Pastikan `user_id` di tabel mesins valid

```bash
# Cek data
php artisan tinker
>>> $mesins = App\Models\Mesin::with('operator')->get()
>>> foreach($mesins as $m) { echo $m->nama_mesin . " - " . $m->operator->name . "\n"; }
```

---

### Problem 4: Status tidak berubah setelah pengecekan
**Symptoms:** Sudah melakukan pengecekan tapi status tetap "Belum Dicek"

**Diagnosis:**
```bash
php artisan tinker
>>> $mesin = App\Models\Mesin::find(1)
>>> $pengecekan = $mesin->pengecekan()->whereDate('tanggal_pengecekan', today())->first()
>>> echo $pengecekan->status
```

**Possible Causes:**
- Status masih "dalam_proses", belum "selesai"
- Tanggal pengecekan bukan hari ini
- Cache browser

**Solutions:**
1. Pastikan status pengecekan = "selesai":
```bash
php artisan tinker
>>> $pengecekan = App\Models\PengecekanMesin::find(ID)
>>> $pengecekan->status = 'selesai'
>>> $pengecekan->save()
```

2. Clear browser cache (Ctrl+Shift+Delete)
3. Hard refresh (Ctrl+F5)

---

### Problem 5: Widget tidak tampil
**Symptoms:** Widget statistik tidak muncul di header

**Check:**
```php
// File: app/Filament/Resources/MonitoringPengecekanResource/Pages/ListMonitoringPengecekan.php

protected function getHeaderWidgets(): array
{
    return [
        StatusPengecekanOverview::class,  // Pastikan ada ini
    ];
}
```

**Solutions:**
```bash
php artisan cache:clear
php artisan view:clear
```

---

### Problem 6: Auto-refresh tidak bekerja
**Symptoms:** Tabel tidak update otomatis setiap 30 detik

**Check:**
- Internet connection active?
- Browser console ada error?
- JavaScript enabled?

**Solutions:**
1. Check browser console (F12)
2. Disable ad-blocker/firewall yang mungkin block
3. Hard refresh browser
4. Test di browser berbeda

---

### Problem 7: Error "500 Internal Server Error"
**Symptoms:** Error 500 saat akses menu

**Check Log:**
```bash
# Lihat error log
tail -n 50 storage/logs/laravel.log

# Atau
php artisan tinker
>>> app('log')->error('Testing log')
```

**Common Causes:**
- Database connection error
- Missing data/relation
- Permission issue

**Solutions:**
```bash
# Cek database connection
php artisan tinker
>>> DB::connection()->getPdo()

# Cek error detail
php artisan optimize:clear
# Akses lagi dan lihat error message
```

---

### Problem 8: Operator tidak muncul/null
**Symptoms:** Kolom "Operator Bertanggung Jawab" kosong/null

**Diagnosis:**
```bash
php artisan tinker
>>> $mesin = App\Models\Mesin::find(1)
>>> echo $mesin->operator ? $mesin->operator->name : 'NULL'
```

**Possible Causes:**
- `user_id` di tabel mesins adalah NULL
- User dengan id tersebut sudah dihapus
- Relasi tidak benar

**Solutions:**
```bash
# Assign operator ke mesin
php artisan tinker
>>> $mesin = App\Models\Mesin::find(ID_MESIN)
>>> $mesin->user_id = ID_USER
>>> $mesin->save()
```

---

### Problem 9: Duplicate entry error
**Symptoms:** 
```
SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry
```

**Cause:** Mencoba membuat pengecekan kedua untuk mesin yang sama di hari yang sama

**Solution:** Ini adalah behavior yang diinginkan. Satu mesin hanya bisa dicek sekali per hari.
- Edit pengecekan yang sudah ada
- Atau hapus pengecekan lama (jika memang salah input)

---

### Problem 10: Performance lambat
**Symptoms:** Tabel loading lama, especially dengan banyak data

**Optimization:**
```bash
# 1. Pastikan index database optimal
php artisan migrate:status

# 2. Optimize queries (sudah implemented eager loading)

# 3. Clear cache
php artisan cache:clear

# 4. Tambah pagination (sudah default di Filament)
```

**Advanced:** Jika data sangat banyak (>1000 mesin), consider:
- Increase pagination size
- Add database index on frequently queried columns
- Use database query caching

---

## 🛠️ Maintenance Commands

### Daily Maintenance
```bash
# Clear cache (jika ada issue)
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Weekly Maintenance
```bash
# Optimize aplikasi
php artisan optimize

# Check log size
ls -lh storage/logs/

# Rotate logs if too large
> storage/logs/laravel.log
```

### Monthly Maintenance
```bash
# Backup database
php artisan db:backup  # (jika ada)

# Clean old data (optional)
php artisan tinker
>>> App\Models\PengecekanMesin::where('tanggal_pengecekan', '<', now()->subDays(90))->delete()
```

---

## 🐛 Debug Mode

Untuk debugging, enable debug mode di `.env`:
```env
APP_DEBUG=true
APP_ENV=local
```

**⚠️ WARNING:** Jangan enable debug mode di production!

---

## 📞 Getting Help

Jika masalah masih berlanjut:

1. **Check Documentation:**
   - MONITORING_README.md
   - MONITORING_SUMMARY.md
   - MONITORING_QUICKSTART.md

2. **Check Logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Test in Tinker:**
   ```bash
   php artisan tinker
   # Test queries manually
   ```

4. **Contact Support:**
   - Check Laravel logs
   - Provide error message
   - Include steps to reproduce

---

## ✅ Quick Diagnostic Checklist

Jika ada masalah, cek list ini:

- [ ] Cache sudah di-clear?
- [ ] Database connection OK?
- [ ] Ada data mesin di database?
- [ ] Relasi operator sudah benar?
- [ ] Route terdaftar?
- [ ] Permission/role sudah sesuai?
- [ ] Browser cache di-clear?
- [ ] JavaScript enabled?
- [ ] Internet connection OK?
- [ ] Log error sudah dicek?

---

## 📊 Performance Benchmarks

**Expected Performance:**
- Load time: < 2 seconds (100 mesin)
- Auto-refresh: Seamless (30s interval)
- Search: Instant (<100ms)
- Filter: Instant (<100ms)

**If slower:** Check database indexes and server resources.

---

**Last Updated:** 25 Januari 2026  
**Version:** 1.0  
**Status:** Production Ready ✅
