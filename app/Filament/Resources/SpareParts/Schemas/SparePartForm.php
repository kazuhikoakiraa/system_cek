<?php

namespace App\Filament\Resources\SpareParts\Schemas;

use App\Models\SparePartCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;

class SparePartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->tabs([
                        // Tab 1: Informasi Dasar
                        Tabs\Tab::make('Informasi Dasar')
                            ->schema([
                                Section::make('Informasi Suku Cadang')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('kode_suku_cadang')
                                                    ->label('Kode Suku Cadang')
                                                    ->required()
                                                    ->unique(ignoreRecord: true)
                                                    ->maxLength(255)
                                                    ->placeholder('Contoh: SC-001'),

                                                TextInput::make('nama_suku_cadang')
                                                    ->label('Nama Suku Cadang')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpan(1),

                                                Select::make('category_id')
                                                    ->label('Kategori')
                                                    ->options(SparePartCategory::all()->pluck('nama_kategori', 'id'))
                                                    ->searchable()
                                                    ->preload()
                                                    ->createOptionForm([
                                                        TextInput::make('kode_kategori')
                                                            ->label('Kode Kategori')
                                                            ->required()
                                                            ->maxLength(10),
                                                        TextInput::make('nama_kategori')
                                                            ->label('Nama Kategori')
                                                            ->required(),
                                                        Textarea::make('deskripsi')
                                                            ->label('Deskripsi'),
                                                    ])
                                                    ->createOptionUsing(function ($data) {
                                                        return SparePartCategory::create($data)->id;
                                                    })
                                                    ->required(),

                                                Select::make('status')
                                                    ->label('Status')
                                                    ->options([
                                                        'active' => 'Aktif',
                                                        'inactive' => 'Tidak Aktif',
                                                    ])
                                                    ->default('active')
                                                    ->required(),
                                            ]),

                                        Textarea::make('deskripsi')
                                            ->label('Deskripsi')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        Textarea::make('spesifikasi_teknis')
                                            ->label('Spesifikasi Teknis')
                                            ->rows(3)
                                            ->placeholder('Contoh: Voltage: 220V, Power: 1.5kW, Material: Stainless Steel')
                                            ->columnSpanFull(),

                                        FileUpload::make('foto')
                                            ->label('Foto Suku Cadang')
                                            ->image()
                                            ->directory('spare-parts')
                                            ->maxSize(2048)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 2: Stok & Persediaan
                        Tabs\Tab::make('Stok & Persediaan')
                            ->schema([
                                Section::make('Manajemen Stok')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('stok')
                                                    ->label('Stok Saat Ini')
                                                    ->required()
                                                    ->numeric()
                                                    ->default(0)
                                                    ->minValue(0)
                                                    ->helperText('Stok akan otomatis terupdate dari transaksi'),

                                                TextInput::make('stok_minimum')
                                                    ->label('Stok Minimum')
                                                    ->required()
                                                    ->numeric()
                                                    ->default(10)
                                                    ->minValue(0)
                                                    ->helperText('Peringatan akan muncul jika stok dibawah nilai ini'),

                                                TextInput::make('stok_maksimum')
                                                    ->label('Stok Maksimum')
                                                    ->required()
                                                    ->numeric()
                                                    ->default(100)
                                                    ->minValue(0)
                                                    ->helperText('Batas maksimum stok yang direkomendasikan'),
                                            ]),

                                        Grid::make(2)
                                            ->schema([
                                                Select::make('satuan')
                                                    ->label('Satuan')
                                                    ->options([
                                                        'pcs' => 'Buah (Pcs)',
                                                        'unit' => 'Unit',
                                                        'set' => 'Set',
                                                        'box' => 'Kotak (Box)',
                                                        'pack' => 'Pak (Pack)',
                                                        'meter' => 'Meter',
                                                        'liter' => 'Liter',
                                                        'kg' => 'Kilogram',
                                                        'roll' => 'Roll',
                                                        'sheet' => 'Lembar (Sheet)',
                                                    ])
                                                    ->default('pcs')
                                                    ->required(),

                                                TextInput::make('lokasi_penyimpanan')
                                                    ->label('Lokasi Penyimpanan')
                                                    ->maxLength(255)
                                                    ->placeholder('Contoh: Rak A-12, Gudang Utama'),
                                            ]),

                                        TextInput::make('harga_satuan')
                                            ->label('Harga Satuan')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->minValue(0)
                                            ->helperText('Harga per satuan untuk perhitungan nilai aset'),
                                    ]),
                            ]),

                        // Tab 3: Pengadaan & Pemasok
                        Tabs\Tab::make('Pengadaan & Pemasok')
                            ->schema([
                                Section::make('Informasi Pengadaan')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('supplier')
                                                    ->label('Pemasok (Supplier)')
                                                    ->maxLength(255)
                                                    ->placeholder('Nama pemasok/vendor'),

                                                DatePicker::make('tanggal_pengadaan')
                                                    ->label('Tanggal Pengadaan')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y'),

                                                Select::make('tahun_pengadaan')
                                                    ->label('Tahun Pengadaan')
                                                    ->options(function () {
                                                        $years = [];
                                                        for ($i = date('Y'); $i >= date('Y') - 10; $i--) {
                                                            $years[$i] = $i;
                                                        }
                                                        return $years;
                                                    })
                                                    ->searchable(),
                                            ]),
                                    ]),
                            ]),

                        // Tab 4: Garansi
                        Tabs\Tab::make('Garansi')
                            ->schema([
                                Section::make('Informasi Garansi')
                                    ->description('Monitoring garansi suku cadang')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                DatePicker::make('tanggal_warranty_mulai')
                                                    ->label('Tanggal Garansi Mulai')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        $warrantyBulan = $get('warranty_bulan');
                                                        if ($state && $warrantyBulan) {
                                                            $expired = \Carbon\Carbon::parse($state)->addMonths($warrantyBulan);
                                                            $set('tanggal_warranty_expired', $expired->format('Y-m-d'));
                                                        }
                                                    }),

                                                TextInput::make('warranty_bulan')
                                                    ->label('Durasi Garansi (Bulan)')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->placeholder('Contoh: 12')
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        $startDate = $get('tanggal_warranty_mulai');
                                                        if ($startDate && $state) {
                                                            $expired = \Carbon\Carbon::parse($startDate)->addMonths($state);
                                                            $set('tanggal_warranty_expired', $expired->format('Y-m-d'));
                                                        }
                                                    }),

                                                DatePicker::make('tanggal_warranty_expired')
                                                    ->label('Tanggal Garansi Berakhir')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y'),
                                            ]),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
