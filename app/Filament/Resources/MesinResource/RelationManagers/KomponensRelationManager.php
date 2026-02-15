<?php

namespace App\Filament\Resources\MesinResource\RelationManagers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;

class KomponensRelationManager extends RelationManager
{
    protected static string $relationship = 'komponens';

    protected static ?string $title = 'Komponen Mesin';

    protected static ?string $recordTitleAttribute = 'nama_komponen';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextInput::make('nama_komponen')
                    ->label('Nama Komponen')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Motor Listrik, Bearing, dll')
                    ->columnSpan(1),

                TextInput::make('manufacturer')
                    ->label('Manufaktur/Merek')
                    ->maxLength(255)
                    ->placeholder('Contoh: Siemens, SKF, dll')
                    ->columnSpan(1),

                TextInput::make('part_number')
                    ->label('Part Number')
                    ->maxLength(255)
                    ->placeholder('Contoh: ABC-123-XYZ')
                    ->columnSpan(1),

                TextInput::make('lokasi_pemasangan')
                    ->label('Lokasi Pemasangan')
                    ->maxLength(255)
                    ->placeholder('Contoh: Bagian Motor Kiri')
                    ->columnSpan(1),

                DatePicker::make('tanggal_pengadaan')
                    ->label('Tanggal Pengadaan')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->columnSpan(1),

                TextInput::make('jadwal_ganti_bulan')
                    ->label('Jadwal Ganti (Bulan)')
                    ->numeric()
                    ->minValue(1)
                    ->suffix('bulan')
                    ->helperText('Interval penggantian dalam bulan')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && $get('tanggal_perawatan_terakhir')) {
                            $lastMaintenance = \Carbon\Carbon::parse($get('tanggal_perawatan_terakhir'));
                            $nextChange = $lastMaintenance->copy()->addMonths($state);
                            $set('estimasi_tanggal_ganti_berikutnya', $nextChange->format('Y-m-d'));
                        }
                    })
                    ->columnSpan(1),

                DatePicker::make('tanggal_perawatan_terakhir')
                    ->label('Tanggal Perawatan Terakhir')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state && $get('jadwal_ganti_bulan')) {
                            $lastMaintenance = \Carbon\Carbon::parse($state);
                            $nextChange = $lastMaintenance->copy()->addMonths($get('jadwal_ganti_bulan'));
                            $set('estimasi_tanggal_ganti_berikutnya', $nextChange->format('Y-m-d'));
                        }
                    })
                    ->columnSpan(1),

                DatePicker::make('estimasi_tanggal_ganti_berikutnya')
                    ->label('Estimasi Tanggal Ganti Berikutnya')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->helperText('Otomatis dihitung berdasarkan perawatan terakhir + jadwal ganti')
                    ->columnSpan(1),

                TextInput::make('nama_supplier')
                    ->label('Nama Supplier')
                    ->maxLength(255)
                    ->placeholder('Nama perusahaan supplier')
                    ->columnSpan(1),

                TextInput::make('harga_komponen')
                    ->label('Harga Komponen')
                    ->numeric()
                    ->prefix('Rp')
                    ->placeholder('0')
                    ->columnSpan(1),

                TextInput::make('jumlah_terpasang')
                    ->label('Jumlah Terpasang')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->columnSpan(1),

                TextInput::make('stok_minimal')
                    ->label('Stok Minimal')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->helperText('Minimal stok spare part yang harus tersedia')
                    ->columnSpan(1),

                Select::make('status_komponen')
                    ->label('Status Komponen')
                    ->options([
                        'normal' => '✅ Normal',
                        'perlu_ganti' => '⚠️ Perlu Ganti',
                        'rusak' => '❌ Rusak',
                    ])
                    ->default('normal')
                    ->required()
                    ->native(false)
                    ->columnSpan(2),

                Textarea::make('spesifikasi_teknis')
                    ->label('Spesifikasi Teknis')
                    ->rows(3)
                    ->placeholder('Detail spesifikasi teknis komponen...')
                    ->columnSpan(2),

                Textarea::make('catatan')
                    ->label('Catatan')
                    ->rows(3)
                    ->placeholder('Catatan tambahan tentang komponen...')
                    ->columnSpan(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_komponen')
                    ->label('Nama Komponen')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->description(fn ($record) => $record->part_number),

                TextColumn::make('manufacturer')
                    ->label('Manufaktur')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('lokasi_pemasangan')
                    ->label('Lokasi')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('status_komponen')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'normal' => 'success',
                        'perlu_ganti' => 'warning',
                        'rusak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'normal' => '✅ Normal',
                        'perlu_ganti' => '⚠️ Perlu Ganti',
                        'rusak' => '❌ Rusak',
                        default => $state,
                    }),

                TextColumn::make('jadwal_ganti_bulan')
                    ->label('Jadwal Ganti')
                    ->suffix(' bulan')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('tanggal_perawatan_terakhir')
                    ->label('Perawatan Terakhir')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('estimasi_tanggal_ganti_berikutnya')
                    ->label('Estimasi Ganti Berikutnya')
                    ->date('d M Y')
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record->estimasi_tanggal_ganti_berikutnya) return 'gray';
                        $date = \Carbon\Carbon::parse($record->estimasi_tanggal_ganti_berikutnya);
                        if ($date->isPast()) return 'danger';
                        if ($date->diffInDays(now()) < 30) return 'warning';
                        return 'success';
                    })
                    ->description(function ($record) {
                        if (!$record->estimasi_tanggal_ganti_berikutnya) return null;
                        $date = \Carbon\Carbon::parse($record->estimasi_tanggal_ganti_berikutnya);
                        if ($date->isPast()) return '⚠️ Sudah Melewati Jadwal';
                        $days = $date->diffInDays(now());
                        if ($days < 30) return "⏰ {$days} hari lagi";
                        return null;
                    }),

                TextColumn::make('harga_komponen')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('jumlah_terpasang')
                    ->label('Terpasang')
                    ->suffix(' unit')
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('Tambah Komponen')
                    ->icon('heroicon-o-plus'),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
