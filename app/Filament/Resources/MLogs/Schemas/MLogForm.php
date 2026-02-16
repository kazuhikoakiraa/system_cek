<?php

namespace App\Filament\Resources\MLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Illuminate\Support\Facades\Auth;

class MLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        // Tab 1: Informasi Dasar
                        Tabs\Tab::make('Informasi Dasar')
                            ->schema([
                                Section::make('Detail Pekerjaan')
                                    ->description('Informasi pekerjaan maintenance')
                                    ->icon('heroicon-o-wrench-screwdriver')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('m_request_id')
                                                    ->label('Request Maintenance')
                                                    ->relationship(
                                                        'request',
                                                        'request_number',
                                                        fn ($query) => $query->where('status', '!=', 'completed')
                                                    )
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->helperText('Pilih request yang akan dikerjakan'),
                                                
                                                Select::make('teknisi_id')
                                                    ->label('Teknisi')
                                                    ->relationship(
                                                        'teknisi',
                                                        'name',
                                                        fn ($query) => $query->where(function ($q) {
                                                            $q->whereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('name', ['Teknisi', 'Super Admin']));
                                                        })
                                                    )
                                                    ->searchable()
                                                    ->preload()
                                                    ->required()
                                                    ->default(fn () => Auth::id()),
                                                
                                                DateTimePicker::make('tanggal_mulai')
                                                    ->label('Tanggal Mulai Pekerjaan')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->seconds(false)
                                                    ->default(now()),
                                                
                                                DateTimePicker::make('tanggal_selesai')
                                                    ->label('Tanggal Selesai Pekerjaan')
                                                    ->native(false)
                                                    ->displayFormat('d/m/Y H:i')
                                                    ->seconds(false)
                                                    ->after('tanggal_mulai'),
                                                
                                                Select::make('status')
                                                    ->label('Status Pekerjaan')
                                                    ->options([
                                                        'in_progress' => '🔧 Dalam Pengerjaan',
                                                        'submitted' => '📋 Submitted (Menunggu Approval)',
                                                        'completed' => '✅ Selesai',
                                                    ])
                                                    ->default('in_progress')
                                                    ->required()
                                                    ->native(false),
                                            ]),
                                    ]),
                            ]),

                        // Tab 2: Dokumentasi
                        Tabs\Tab::make('Dokumentasi')
                            ->schema([
                                Section::make('Foto Kondisi')
                                    ->description('Foto sebelum & sesudah perbaikan')
                                    ->icon('heroicon-o-camera')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                FileUpload::make('foto_sebelum')
                                                    ->label('Foto Sebelum Perbaikan')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->directory('maintenance-logs')
                                                    ->maxSize(5120)
                                                    ->helperText('Format: JPG, PNG. Max 5MB'),
                                                
                                                FileUpload::make('foto_sesudah')
                                                    ->label('Foto Sesudah Perbaikan')
                                                    ->image()
                                                    ->imageEditor()
                                                    ->directory('maintenance-logs')
                                                    ->maxSize(5120)
                                                    ->helperText('Format: JPG, PNG. Max 5MB'),
                                            ]),
                                    ]),

                                Section::make('Catatan Teknisi')
                                    ->description('Detail pekerjaan yang dilakukan')
                                    ->icon('heroicon-o-document-text')
                                    ->schema([
                                        Textarea::make('catatan_teknisi')
                                            ->label('Catatan Pekerjaan')
                                            ->rows(5)
                                            ->placeholder('Jelaskan pekerjaan yang dilakukan, hasil, dan temuan...')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // Tab 3: Suku Cadang
                        Tabs\Tab::make('Suku Cadang')
                            ->badge(fn ($record) => $record ? $record->spareParts()->count() : null)
                            ->schema([
                                Section::make('Suku Cadang yang Digunakan')
                                    ->description('Daftar spare parts yang dipakai dalam pekerjaan ini')
                                    ->icon('heroicon-o-cube')
                                    ->schema([
                                        Repeater::make('spare_parts_data')
                                            ->label('')
                                            ->schema([
                                                Select::make('spare_part_id')
                                                    ->label('Nama Suku Cadang')
                                                    ->options(function () {
                                                        return \App\Models\SparePart::query()
                                                            ->pluck('nama_suku_cadang', 'id');
                                                    })
                                                    ->searchable()
                                                    ->required()
                                                    ->live()
                                                    ->afterStateUpdated(function ($state, callable $set) {
                                                        if ($state) {
                                                            $sparePart = \App\Models\SparePart::find($state);
                                                            if ($sparePart) {
                                                                $set('harga_satuan', $sparePart->harga_satuan);
                                                            }
                                                        }
                                                    })
                                                    ->columnSpan(2),
                                                
                                                TextInput::make('jumlah_digunakan')
                                                    ->label('Jumlah')
                                                    ->numeric()
                                                    ->required()
                                                    ->default(1)
                                                    ->minValue(1),
                                                
                                                TextInput::make('harga_satuan')
                                                    ->label('Harga Satuan (Rp)')
                                                    ->numeric()
                                                    ->prefix('Rp')
                                                    ->disabled()
                                                    ->dehydrated(),
                                                
                                                Textarea::make('catatan')
                                                    ->label('Catatan')
                                                    ->rows(2)
                                                    ->placeholder('Catatan penggunaan spare part...'),
                                            ])
                                            ->columns(4)
                                            ->defaultItems(0)
                                            ->addActionLabel('Tambah Suku Cadang')
                                            ->collapsible()
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
