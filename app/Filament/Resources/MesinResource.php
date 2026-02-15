<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MesinResource\Pages;
use App\Filament\Resources\MesinResource\RelationManagers\KomponensRelationManager;
use App\Filament\Resources\MesinResource\RelationManagers\RequestsRelationManager;
use App\Filament\Resources\MesinResource\RelationManagers\AuditsRelationManager;
use App\Models\Mesin;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\Textarea as FormTextarea;
use Filament\Forms\Components\TextInput as FormTextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MesinResource extends Resource
{
    protected static ?string $model = Mesin::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog;

    protected static ?string $navigationLabel = 'Master Mesin';

    protected static ?string $pluralModelLabel = 'Master Mesin';

    protected static ?string $modelLabel = 'Mesin';

    protected static string|\UnitEnum|null $navigationGroup = 'Manajemen Mesin';

    protected static ?int $navigationSort = 1;

    protected static ?int $navigationGroupSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        // Tab 1: Informasi Dasar
                        Tabs\Tab::make('Informasi Dasar')
                            ->schema([
                                Section::make('Identitas Mesin')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                FormTextInput::make('kode_mesin')
                                                    ->label('Kode Mesin')
                                                    ->required()
                                                    ->maxLength(100)
                                                    ->unique(ignoreRecord: true)
                                                    ->placeholder('Contoh: MSN-001')
                                                    ->helperText('Kode unik untuk identifikasi mesin'),

                                                FormTextInput::make('serial_number')
                                                    ->label('Serial Number')
                                                    ->maxLength(100)
                                                    ->placeholder('SN dari manufaktur'),

                                                FormTextInput::make('nama_mesin')
                                                    ->label('Nama Mesin')
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->placeholder('Contoh: Mesin CNC 001')
                                                    ->columnSpan(2),

                                                FormTextInput::make('manufacturer')
                                                    ->label('Manufaktur/Pabrikan')
                                                    ->maxLength(255)
                                                    ->placeholder('Contoh: Siemens, Fanuc, dll'),

                                                FormTextInput::make('model_number')
                                                    ->label('Model/Tipe')
                                                    ->maxLength(255)
                                                    ->placeholder('Model atau tipe mesin'),

                                                FormTextInput::make('jenis_mesin')
                                                    ->label('Jenis Mesin')
                                                    ->maxLength(100)
                                                    ->placeholder('Contoh: CNC, Forklift, dll'),

                                                FormTextInput::make('tahun_pembuatan')
                                                    ->label('Tahun Pembuatan')
                                                    ->numeric()
                                                    ->minValue(1900)
                                                    ->maxValue(date('Y'))
                                                    ->placeholder('YYYY'),

                                                FormTextInput::make('lokasi_instalasi')
                                                    ->label('Lokasi Instalasi')
                                                    ->maxLength(255)
                                                    ->placeholder('Contoh: Lantai 2, Area Produksi')
                                                    ->columnSpan(2),

                                                FormSelect::make('status')
                                                    ->label('Status')
                                                    ->options([
                                                        'aktif' => '✅ Aktif',
                                                        'nonaktif' => '⏸️ Non-Aktif',
                                                        'maintenance' => '🔧 Maintenance',
                                                        'rusak' => '❌ Rusak',
                                                    ])
                                                    ->default('aktif')
                                                    ->required()
                                                    ->native(false),

                                                FormTextInput::make('kondisi_terakhir')
                                                    ->label('Kondisi Terakhir')
                                                    ->maxLength(100)
                                                    ->placeholder('Contoh: Baik, Perlu Perhatian'),

                                                FormSelect::make('user_id')
                                                    ->label('Penanggung Jawab')
                                                    ->options(
                                                        User::all()->pluck('name', 'id')
                                                    )
                                                    ->searchable()
                                                    ->preload()
                                                    ->nullable()
                                                    ->helperText('Operator atau teknisi yang bertanggung jawab')
                                                    ->columnSpan(2),
                                            ]),
                                    ]),
                            ]),

                        // Tab 2: Pengadaan & Keuangan
                        Tabs\Tab::make('Pengadaan & Keuangan')
                            ->schema([
                                Section::make('Informasi Pengadaan')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                DatePicker::make('tanggal_pengadaan')
                                                    ->label('Tanggal Pengadaan')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        if ($state && $get('umur_ekonomis_bulan')) {
                                                            $bulan = $get('umur_ekonomis_bulan');
                                                            $estimasi = \Carbon\Carbon::parse($state)->addMonths($bulan);
                                                            $set('estimasi_penggantian', $estimasi->format('Y-m-d'));
                                                        }
                                                    }),

                                                FormTextInput::make('harga_pengadaan')
                                                    ->label('Harga Pengadaan')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->placeholder('0'),

                                                FormTextInput::make('nomor_invoice')
                                                    ->label('Nomor Invoice/PO')
                                                    ->maxLength(255)
                                                    ->placeholder('INV-XXXX'),

                                                FormTextInput::make('supplier')
                                                    ->label('Supplier/Vendor')
                                                    ->maxLength(255)
                                                    ->placeholder('Nama perusahaan supplier')
                                                    ->columnSpan(3),
                                            ]),
                                    ]),

                                Section::make('Umur Ekonomis')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                FormTextInput::make('umur_ekonomis_bulan')
                                                    ->label('Umur Ekonomis')
                                                    ->numeric()
                                                    ->minValue(1)
                                                    ->suffix('bulan')
                                                    ->helperText('Estimasi umur pakai mesin dalam bulan')
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        if ($state && $get('tanggal_pengadaan')) {
                                                            $tanggal = \Carbon\Carbon::parse($get('tanggal_pengadaan'));
                                                            $estimasi = $tanggal->copy()->addMonths($state);
                                                            $set('estimasi_penggantian', $estimasi->format('Y-m-d'));
                                                        }
                                                    }),

                                                DatePicker::make('estimasi_penggantian')
                                                    ->label('Estimasi Penggantian')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y')
                                                    ->helperText('Otomatis dihitung dari tanggal pengadaan + umur ekonomis'),
                                            ]),
                                    ]),
                            ]),

                        // Tab 3: Garansi
                        Tabs\Tab::make('Garansi')
                            ->schema([
                                Section::make('Informasi Garansi')
                                    ->schema([
                                        DatePicker::make('tanggal_waranty_expired')
                                            ->label('Tanggal Berakhir Garansi')
                                            ->native(false)
                                            ->displayFormat('d/m/Y')
                                            ->helperText('Tanggal berakhirnya masa garansi mesin'),
                                    ]),
                            ]),

                        // Tab 4: Spesifikasi & Dokumentasi
                        Tabs\Tab::make('Spesifikasi & Dokumentasi')
                            ->schema([
                                Section::make('Spesifikasi')
                                    ->schema([
                                        FormTextarea::make('spesifikasi_teknis')
                                            ->label('Spesifikasi Teknis')
                                            ->rows(4)
                                            ->placeholder('Spesifikasi teknis lengkap mesin...')
                                            ->columnSpanFull(),

                                        FormTextarea::make('catatan')
                                            ->label('Catatan Tambahan')
                                            ->rows(3)
                                            ->placeholder('Catatan penting tentang mesin...')
                                            ->columnSpanFull(),
                                    ]),

                                Section::make('Dokumentasi')
                                    ->schema([
                                        FileUpload::make('foto')
                                            ->label('Foto Mesin')
                                            ->image()
                                            ->imageEditor()
                                            ->imageEditorAspectRatios([
                                                '16:9',
                                                '4:3',
                                                '1:1',
                                            ])
                                            ->directory('mesin-photos')
                                            ->maxSize(5120)
                                            ->helperText('Format: JPG, PNG. Max 5MB')
                                            ->columnSpanFull(),

                                        FormTextarea::make('dokumen_pendukung')
                                            ->label('Dokumen Pendukung')
                                            ->rows(3)
                                            ->placeholder('Link atau deskripsi dokumen pendukung (Manual, Sertifikat, SOP, dll)...')
                                            ->helperText('Anda dapat menyimpan link Google Drive atau deskripsi lokasi dokumen fisik')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-machine.png')),

                TextColumn::make('kode_mesin')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('nama_mesin')
                    ->label('Nama Mesin')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->jenis_mesin),

                TextColumn::make('lokasi_instalasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aktif' => 'success',
                        'nonaktif' => 'gray',
                        'maintenance' => 'warning',
                        'rusak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'aktif' => '✅ Aktif',
                        'nonaktif' => '⏸️ Non-Aktif',
                        'maintenance' => '🔧 Maintenance',
                        'rusak' => '❌ Rusak',
                        default => $state,
                    }),

                TextColumn::make('pemilik.name')
                    ->label('Penanggung Jawab')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('kondisi_terakhir')
                    ->label('Kondisi')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('tanggal_pengadaan')
                    ->label('Tgl Pengadaan')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Non-Aktif',
                        'maintenance' => 'Maintenance',
                        'rusak' => 'Rusak',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Penanggung Jawab')
                    ->relationship('pemilik', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),
                EditAction::make()
                    ->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Mesin Terpilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            KomponensRelationManager::class,
            RequestsRelationManager::class,
            AuditsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMesins::route('/'),
            'create' => Pages\CreateMesin::route('/create'),
            'view' => Pages\ViewMesin::route('/{record}'),
            'edit' => Pages\EditMesin::route('/{record}/edit'),
        ];
    }
}
