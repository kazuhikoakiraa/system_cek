# Sistem Manajemen Mesin - REVISI

Sistem untuk mengelola data mesin dengan multiple komponen dan standar pengecekan yang berbeda untuk setiap komponen.

## Fitur Utama

- ✅ **CRUD Mesin Lengkap** - Tambah, lihat, edit, dan hapus data mesin
- ✅ **Multiple Komponen per Mesin** - Setiap mesin bisa punya banyak komponen
- ✅ **Standar & Frekuensi per Komponen** - Setiap komponen punya standar dan frekuensi pengecekan sendiri
- ✅ **Operator dari Role** - Operator dipilih dari user dengan role "operator"
- ✅ **Repeater Interface** - Interface yang user-friendly untuk manage multiple komponen
- ✅ **Filter & Pencarian** - Filter berdasarkan operator dan search
- ✅ **Relasi Database** - Proper relasi antara mesin, user, dan komponen

## Struktur Database

### Tabel: `mesins`

| Kolom           | Tipe      | Deskripsi                                    |
|-----------------|-----------|----------------------------------------------|
| id              | bigint    | Primary key                                  |
| nama_mesin      | varchar   | Nama mesin (required)                        |
| user_id         | bigint    | Foreign key ke users table (operator)        |
| deskripsi       | text      | Deskripsi umum mesin (optional)              |
| created_at      | timestamp | Waktu pembuatan record                       |
| updated_at      | timestamp | Waktu update terakhir                        |

### Tabel: `komponen_mesins`

| Kolom           | Tipe      | Deskripsi                                    |
|-----------------|-----------|----------------------------------------------|
| id              | bigint    | Primary key                                  |
| mesin_id        | bigint    | Foreign key ke mesins table                  |
| nama_komponen   | varchar   | Nama komponen (required)                     |
| standar         | varchar   | Standar pengecekan (required)                |
| frekuensi       | enum      | harian/mingguan/bulanan/tahunan (required)   |
| catatan         | text      | Catatan tambahan (optional)                  |
| created_at      | timestamp | Waktu pembuatan record                       |
| updated_at      | timestamp | Waktu update terakhir                        |

### Relasi

- **Mesin** `belongsTo` **User** (operator)
- **Mesin** `hasMany` **KomponenMesin**
- **KomponenMesin** `belongsTo` **Mesin**

## Cara Menggunakan

### 1. Migrasi Database

```bash
php artisan migrate
```

### 2. Buat User Operator (Jika Belum Ada)

```bash
php artisan tinker
```

```php
$user = User::create([
    'name' => 'Operator 1',
    'email' => 'operator1@example.com',
    'password' => bcrypt('password'),
    'email_verified_at' => now(),
]);
$user->assignRole('operator');
```

### 3. Seed Data Dummy (Opsional)

```bash
php artisan db:seed --class=MesinSeeder
```

### 4. Akses Fitur

1. Login ke panel admin Filament: `http://localhost:8000/admin`
2. Klik menu **"Mesin"** di sidebar
3. Klik tombol **"Create"** untuk menambah mesin baru
4. Isi form:
   - **Nama Mesin**: Contoh: Mesin CNC 001
   - **Operator**: Pilih dari dropdown (hanya user dengan role operator)
   - **Deskripsi**: Deskripsi umum mesin
   - **Komponen**: Klik "Tambah Komponen" untuk menambah komponen
     - Nama Komponen: Motor Penggerak, Bearing, dll
     - Frekuensi: Pilih jadwal pengecekan
     - Standar Pengecekan: Kriteria yang harus dipenuhi
     - Catatan: Informasi tambahan per komponen

## Contoh Penggunaan

### Membuat Mesin dengan Komponen
### Membuat Mesin dengan Komponen

```php
$operator = User::role('operator')->first();

$mesin = Mesin::create([
    'nama_mesin' => 'Mesin CNC 001',
    'user_id' => $operator->id,
    'deskripsi' => 'Mesin CNC untuk produksi komponen presisi tinggi'
]);

$mesin->komponenMesins()->createMany([
    [
        'nama_komponen' => 'Motor Penggerak',
        'standar' => 'Tekanan 5-7 bar, Suhu maksimal 80°C',
        'frekuensi' => 'harian',
        'catatan' => 'Cek setiap pagi sebelum operasional'
    ],
    [
        'nama_komponen' => 'Bearing Utama',
        'standar' => 'Grease kuning, tidak ada bunyi abnormal',
        'frekuensi' => 'mingguan',
        'catatan' => null
    ],
]);
```

### Query Data dengan Relasi

```php
// Get mesin dengan operator dan komponennya
$mesin = Mesin::with('operator', 'komponenMesins')->find(1);

// Get semua mesin untuk operator tertentu
$mesins = Mesin::where('user_id', $operatorId)->with('komponenMesins')->get();

// Count komponen per mesin
$mesin = Mesin::withCount('komponenMesins')->find(1);
echo $mesin->komponen_mesins_count;
```

## File Structure

```
app/
├── Models/
│   ├── Mesin.php                                    # Model Mesin dengan relasi
│   └── KomponenMesin.php                            # Model KomponenMesin
└── Filament/
    └── Resources/
        ├── MesinResource.php                        # Main resource dengan Repeater
        └── MesinResource/
            └── Pages/
                ├── ListMesins.php                   # List page
                ├── CreateMesin.php                  # Create page
                ├── EditMesin.php                    # Edit page
                └── ViewMesin.php                    # View page

database/
├── factories/
│   ├── MesinFactory.php                             # Factory untuk Mesin
│   └── KomponenMesinFactory.php                     # Factory untuk Komponen
├── migrations/
│   ├── 2026_01_23_130647_create_mesins_table.php   # Migration tabel mesins
│   └── 2026_01_23_132155_create_komponen_mesins_table.php # Migration tabel komponen
└── seeders/
    └── MesinSeeder.php                              # Seeder data dummy
```

## Fitur Repeater Component

Repeater component memungkinkan penambahan multiple komponen dengan fitur:

- ✅ **Collapsible**: Setiap item bisa di-collapse/expand
- ✅ **Reorderable**: Bisa drag-drop untuk mengubah urutan
- ✅ **Item Label**: Menampilkan nama komponen pada header yang di-collapse
- ✅ **Add/Remove**: Tambah atau hapus komponen dengan mudah
- ✅ **Default Items**: Otomatis menampilkan 1 form komponen saat create

## Filter & Search

### Filter Operator
Filter mesin berdasarkan operator yang bertanggung jawab:
- Dropdown searchable
- Preload data
- Relasi ke user table

### Search
Pencarian global di kolom:
- Nama Mesin
- Nama Operator
- Nama Komponen (melalui relasi)

## Tips Penggunaan

1. **Operator Wajib Ada**: Pastikan sudah ada user dengan role "operator" sebelum membuat mesin
2. **Multiple Komponen**: Gunakan tombol "Tambah Komponen" untuk menambah lebih banyak komponen
3. **Collapse/Expand**: Klik header komponen untuk collapse/expand form
4. **Drag to Reorder**: Drag icon di sebelah kiri untuk mengubah urutan komponen
5. **Badge Display**: Di list, komponen ditampilkan sebagai badge yang bisa di-scroll

## Testing

Test CRUD operations melalui tinker:

```bash
php artisan tinker
```

```php
// Create
$mesin = Mesin::create([...]);

// Read
$mesin = Mesin::find(1);
$all = Mesin::all();

// Update
$mesin->update(['nama_operator' => 'New Name']);

// Delete
$mesin->delete();
```

## Troubleshooting

Jika menemui masalah:

1. Clear cache:
```bash
php artisan optimize:clear
```

2. Re-run migrations:
```bash
php artisan migrate:fresh
```

3. Check logs:
```bash
tail -f storage/logs/laravel.log
```
