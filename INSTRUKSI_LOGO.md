# Instruksi Favicon (Branding via Tab Browser)

Branding aplikasi sekarang menggunakan **favicon saja** (ikon di tab browser). Tidak ada konfigurasi logo untuk navbar/login.

## Langkah-langkah

1. Siapkan file favicon dengan nama `favicon.ico`.
2. Letakkan di:
   ```
   c:\laragon\www\system_cek\public\favicon.ico
   ```

## Catatan
- Filament panel akan otomatis memuat `favicon.ico` via hook head.
- Jika browser masih menampilkan favicon lama, lakukan hard refresh (Ctrl+F5) atau clear cache.

## Logo untuk Export PDF/Excel

Export **PDF** dan **Excel** mendukung penyisipan logo di header.

Urutan file yang akan dicoba (fallback):
1. `public/images/logo.png`
2. `public/images/logo.jpg`
3. `public/images/logo.jpeg`
4. `public/favicon.png`

Rekomendasi: simpan logo perusahaan sebagai `public/images/logo.png` agar konsisten di semua export.
