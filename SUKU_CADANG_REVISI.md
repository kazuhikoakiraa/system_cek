# Revisi Fitur Suku Cadang

## Ringkasan Perubahan

Telah dilakukan revisi pada fitur Suku Cadang sesuai dengan kebutuhan berikut:

### 1. ✅ Penggunaan Bahasa Indonesia

**Yang Diubah:**
- Semua label form, tabel, dan navigasi menggunakan Bahasa Indonesia
- Tab "Stok & Inventory" → "Stok & Persediaan"
- Tab "Pengadaan & Supplier" → "Pengadaan & Pemasok"  
- Tab "Warranty & Garansi" → "Garansi"
- Label field: "Supplier" → "Pemasok", "Warranty" → "Garansi", dll
- Status "discontinued" dihapus, hanya tersisa "Aktif" dan "Tidak Aktif"

### 2. ✅ Kategori dengan Input Kustom

**Fitur yang Sudah Ada:**
- Field kategori sudah mendukung pembuatan kategori baru jika tidak ada di list
- User dapat mengetik kategori baru dengan form popup yang berisi:
  - Kode Kategori
  - Nama Kategori
  - Deskripsi

### 3. ✅ Status Transaksi Keluar Masuk

**Yang Diubah:**
- Konsep "status" di spare part tetap untuk status aktif/tidak aktif
- Tracking keluar masuk barang menggunakan sistem **Transaksi**
- Setiap transaksi mencatat:
  - Tanggal & waktu transaksi
  - Tipe: Masuk, Keluar, atau Retur
  - Jumlah
  - Stok sebelum dan sesudah
  - Keterangan (untuk apa digunakan)
  - User yang menginput

**File Terkait:**
- `SparePartTransaction` model (sudah ada)
- `TransactionsRelationManager` untuk menampilkan riwayat di detail spare part
- `SparePartTransactionResource` untuk mengelola semua transaksi

### 4. ✅ Penghapusan Tab Tracking & Identifikasi

**Yang Dihapus:**
- Tab "Tracking & Identifikasi" dihapus sepenuhnya
- Field yang dihapus dari form:
  - `batch_number`
  - `serial_number`
  - `part_number`
  - `manufacturer`
- Field `spesifikasi_teknis` dipindah ke tab "Informasi Dasar"

**Catatan:** Field tersebut masih ada di database untuk backward compatibility, namun tidak ditampilkan di form.

### 5. ✅ Alur Profesional Menambah Stok

**Cara 1: Quick Action dari Tabel (Rekomendasi untuk update cepat)**
- Di halaman list Suku Cadang, setiap item memiliki action:
  - **Tambah Stok** (icon +) - untuk menambah stok masuk
  - **Kurangi Stok** (icon -) - untuk mengurangi stok keluar
- Form simpel dengan field:
  - Jumlah
  - Tanggal & Waktu
  - Keterangan
- Stok langsung terupdate dan transaksi tercatat otomatis

**Cara 2: Melalui Menu Transaksi Suku Cadang (Rekomendasi untuk pencatatan detail)**
- Menu navigasi baru: "Transaksi Suku Cadang"
- Form lengkap dengan field:
  - Pilih Suku Cadang
  - Tipe Transaksi (Masuk/Keluar/Retur)
  - Jumlah
  - Tanggal & Waktu
  - Keterangan
  - Upload dokumen pendukung
- Menampilkan preview stok sebelum & sesudah
- Validasi stok untuk transaksi keluar

**Cara 3: Dari Detail Suku Cadang**
- Buka detail suku cadang
- Tab "Riwayat Transaksi" menampilkan semua transaksi
- Tombol "Tambah Transaksi" di header tabel
- Sama seperti cara 2 tapi langsung ter-filter ke suku cadang tersebut

## File yang Dimodifikasi

### Diubah:
1. `app/Filament/Resources/SpareParts/Schemas/SparePartForm.php` - Simplifikasi form, hapus tab tracking
2. `app/Filament/Resources/SpareParts/Tables/SparePartsTable.php` - Tambah action quick stock, update label
3. `app/Filament/Resources/SpareParts/SparePartResource.php` - Tambah view page & relation manager
4. `app/Filament/Resources/SpareParts/Pages/ViewSparePart.php` - Tambah relation manager

### Dibuat Baru:
1. `app/Filament/Resources/SpareParts/RelationManagers/TransactionsRelationManager.php` - Menampilkan riwayat transaksi
2. `app/Filament/Resources/SpareParts/SparePartTransactionResource.php` - Resource untuk mengelola transaksi
3. `app/Filament/Resources/SpareParts/Pages/ListSparePartTransactions.php` - Halaman list transaksi
4. `app/Filament/Resources/SpareParts/Pages/CreateSparePartTransaction.php` - Halaman tambah transaksi
5. `app/Filament/Resources/SpareParts/Pages/ViewSparePartTransaction.php` - Halaman detail transaksi
6. `app/Filament/Resources/SpareParts/Pages/EditSparePartTransaction.php` - Halaman edit transaksi

## Struktur Menu Baru

```
Maintenance
├── Suku Cadang
│   ├── List (dengan action Tambah/Kurangi Stok)
│   ├── Create
│   ├── View (dengan tab Riwayat Transaksi)
│   └── Edit
└── Transaksi Suku Cadang (BARU)
    ├── List (semua transaksi)
    ├── Create
    ├── View
    └── Edit (hanya 24 jam pertama)
```

## Fitur Tambahan

### Validasi & Notifikasi:
- Notifikasi sukses saat tambah/kurangi stok
- Validasi stok tidak boleh minus saat transaksi keluar
- Badge warna untuk status stok (merah: habis, kuning: rendah, hijau: normal)

### Tracking:
- User yang menginput tercatat otomatis
- Timestamp transaksi
- Stok sebelum dan sesudah tercatat untuk audit trail

### Permission & Security:
- Edit transaksi hanya bisa dilakukan 24 jam pertama
- Hapus transaksi hanya untuk super admin
- Semua perubahan stok melalui transaksi (tidak bisa edit langsung dari spare part)

## Cara Penggunaan

### Menambah Stok Spare Part yang Sudah Ada:

**Opsi A - Quick Action (Tercepat):**
1. Buka menu "Suku Cadang"
2. Cari spare part yang ingin ditambah
3. Klik tombol "Tambah Stok" (icon +)
4. Isi jumlah dan keterangan
5. Klik "Simpan"

**Opsi B - Dari Menu Transaksi:**
1. Buka menu "Transaksi Suku Cadang"
2. Klik "Tambah Transaksi"
3. Pilih suku cadang
4. Pilih tipe "Masuk"
5. Isi jumlah dan keterangan
6. Klik "Simpan"

**Opsi C - Dari Detail Spare Part:**
1. Buka detail spare part
2. Scroll ke tab "Riwayat Transaksi"
3. Klik "Tambah Transaksi"
4. Isi form transaksi
5. Klik "Simpan"

### Mengurangi Stok (Pemakaian):
1. Sama seperti menambah stok, tapi pilih "Kurangi Stok" atau tipe "Keluar"
2. Wajib isi keterangan untuk apa digunakan
3. Sistem akan validasi apakah stok cukup

### Melihat Riwayat Keluar Masuk:
1. Buka detail spare part
2. Lihat tab "Riwayat Transaksi"
3. Atau buka menu "Transaksi Suku Cadang" untuk melihat semua transaksi

## Catatan Penting

1. **Stok tidak bisa diedit langsung** - Harus melalui transaksi untuk menjaga integritas data
2. **Semua perubahan tercatat** - Audit trail lengkap dengan user dan timestamp
3. **Field tracking dihapus dari form** - Namun tetap ada di database untuk data lama
4. **Kategori fleksibel** - Bisa tambah kategori baru saat input spare part

## Database

Model `SparePartTransaction` sudah ada dengan field:
- `nomor_transaksi` (auto-generate)
- `spare_part_id`
- `tipe_transaksi` (IN/OUT/RETURN)
- `tanggal_transaksi`
- `user_id`
- `jumlah`
- `stok_sebelum`
- `stok_sesudah`
- `keterangan`
- `dokumen`
- `status_approval` (untuk fitur approval jika diperlukan)

Tidak perlu migration karena tabel sudah ada.
