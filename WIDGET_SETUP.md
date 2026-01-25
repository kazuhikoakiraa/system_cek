# Cara Mengaktifkan Widget Monitoring di Dashboard

## Opsi 1: Widget Otomatis Terdeteksi (Recommended)

Panel Anda sudah menggunakan `discoverWidgets()`, sehingga widget akan otomatis tersedia. Untuk menampilkan widget di dashboard, buat custom Dashboard page:

### Langkah 1: Buat Custom Dashboard

Jalankan command:
```bash
php artisan make:filament-page CustomDashboard --type=custom
```

Atau buat manual file `app/Filament/Pages/Dashboard.php`:

```php
<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StatusPengecekanOverview;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            StatusPengecekanOverview::class,
            // Tambahkan widget lain di sini
        ];
    }

    public function getColumns(): int|array
    {
        return 2; // atau [
            // 'sm' => 1,
            // 'md' => 2,
            // 'lg' => 3,
        // ];
    }
}
```

### Langkah 2: Update Panel Provider

Edit `app/Providers/Filament/AdminPanelProvider.php`:

```php
use App\Filament\Pages\Dashboard;  // Import custom Dashboard
use App\Filament\Widgets\StatusPengecekanOverview;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... konfigurasi lain ...
        ->pages([
            Dashboard::class,  // Ini akan override default dashboard
        ])
        ->widgets([
            StatusPengecekanOverview::class,  // Widget akan tersedia global
            // Widget lain...
        ]);
}
```

## Opsi 2: Tambahkan Widget Langsung ke Panel

Edit `app/Providers/Filament/AdminPanelProvider.php`:

```php
use App\Filament\Widgets\StatusPengecekanOverview;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... konfigurasi lain ...
        ->widgets([
            StatusPengecekanOverview::class,  // Tambahkan widget di sini
            AccountWidget::class,
            FilamentInfoWidget::class,
        ]);
}
```

Widget akan muncul di dashboard default.

## Opsi 3: Tambahkan Widget ke Halaman Monitoring

Jika ingin widget muncul di halaman Monitoring Pengecekan itu sendiri:

Edit `app/Filament/Resources/MonitoringPengecekanResource/Pages/ListMonitoringPengecekan.php`:

```php
<?php

namespace App\Filament\Resources\MonitoringPengecekanResource\Pages;

use App\Filament\Resources\MonitoringPengecekanResource;
use App\Filament\Widgets\StatusPengecekanOverview;
use Filament\Resources\Pages\ListRecords;

class ListMonitoringPengecekan extends ListRecords
{
    protected static string $resource = MonitoringPengecekanResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatusPengecekanOverview::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Monitoring Pengecekan Mesin - ' . now()->translatedFormat('d F Y');
    }

    public function getHeading(): string
    {
        return 'Monitoring Pengecekan Mesin';
    }

    public function getSubheading(): ?string
    {
        return 'Daftar status pengecekan mesin untuk hari ini: ' . now()->translatedFormat('l, d F Y');
    }
}
```

Widget akan muncul di bagian header halaman monitoring.

## Verifikasi

Setelah setup, lakukan:

1. **Clear cache**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

2. **Cek widget terdaftar**:
   ```bash
   php artisan filament:list-widgets
   ```

3. **Akses dashboard** dan widget seharusnya muncul

## Kustomisasi Widget

### Ubah Ukuran Widget
Edit `app/Filament/Widgets/StatusPengecekanOverview.php`:

```php
protected function getColumns(): int
{
    return 2; // Jumlah kolom (default: 4)
}

// Atau responsive:
protected function getColumns(): int|array
{
    return [
        'sm' => 1,
        'md' => 2,
        'lg' => 4,
    ];
}
```

### Ubah Urutan Widget
```php
protected static ?int $sort = 1; // Semakin kecil, semakin atas
```

### Full Width Widget
```php
protected int | string | array $columnSpan = 'full';
```

## Troubleshooting

### Widget tidak muncul
1. Clear semua cache
2. Pastikan class widget sudah benar
3. Cek namespace dan import
4. Restart development server jika menggunakan `php artisan serve`

### Error "Class not found"
```bash
composer dump-autoload
php artisan optimize:clear
```

### Widget muncul tapi kosong
- Pastikan ada data mesin di database
- Cek query di widget tidak error
- Check browser console untuk error JavaScript

## Rekomendasi

**Untuk Supervisor/Manager**: Gunakan Opsi 3 - Tambahkan widget di halaman monitoring itu sendiri, sehingga statistik dan detail berada di satu halaman.

**Untuk Dashboard Overview**: Gunakan Opsi 1 atau 2 - Tampilkan widget di dashboard utama untuk quick glance status pengecekan.
