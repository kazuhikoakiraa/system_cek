<?php

namespace App\Filament\Resources\MaintenanceReports\Schemas;

use App\Models\KomponenMesin;
use App\Models\DaftarPengecekan;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MaintenanceReportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Issue')
                    ->schema([
                        Select::make('mesin_id')
                            ->label('Mesin')
                            ->relationship('mesin', 'nama_mesin')
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($record) => $record !== null),

                        Select::make('komponen_mesin_id')
                            ->label('Komponen Mesin')
                            ->options(fn ($get) => $get('mesin_id') 
                                ? KomponenMesin::where('mesin_id', $get('mesin_id'))
                                    ->pluck('nama_komponen', 'id')
                                : []
                            )
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->disabled(fn ($record) => $record !== null),

                        Hidden::make('detail_pengecekan_mesin_id'),

                        Textarea::make('issue_description')
                            ->label('Deskripsi Masalah')
                            ->required()
                            ->rows(3)
                            ->disabled(fn ($record) => $record !== null)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Menunggu',
                                'in_progress' => 'Sedang Diproses',
                                'completed' => 'Selesai',
                            ])
                            ->required()
                            ->default('pending')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Dokumentasi Awal')
                    ->description('Upload foto kondisi sebelum maintenance (Wajib untuk memulai proses)')
                    ->schema([
                        FileUpload::make('foto_sebelum')
                            ->label('Foto Sebelum Maintenance')
                            ->image()
                            ->directory('maintenance/before')
                            ->imageEditor()
                            ->required(fn ($record) => $record && $record->status === 'pending')
                            ->disabled(fn ($record) => $record && $record->status === 'completed'),

                        Placeholder::make('info_foto_sebelum')
                            ->label('')
                            ->content('Setelah upload foto kondisi awal, status akan berubah menjadi "In Progress" dan Anda dapat melanjutkan proses maintenance.')
                            ->visible(fn ($record) => !$record || $record->status === 'pending'),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record !== null),

                Section::make('Proses Maintenance')
                    ->schema([
                        Select::make('teknisi_id')
                            ->label('Teknisi')
                            ->relationship('teknisi', 'name')
                            ->default(fn () => Auth::id())
                            ->required()
                            ->searchable()
                            ->preload()
                            ->disabled(fn ($record) => $record && $record->status === 'completed'),

                        Textarea::make('catatan_teknisi')
                            ->label('Catatan Teknisi')
                            ->rows(3)
                            ->disabled(fn ($record) => $record && $record->status === 'completed')
                            ->columnSpanFull(),

                        Repeater::make('spare_parts_data')
                            ->label('Suku Cadang yang Digunakan')
                            ->schema([
                                Select::make('spare_part_id')
                                    ->label('Suku Cadang')
                                    ->options(SparePart::pluck('nama_suku_cadang', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $sparePart = SparePart::find($state);
                                            if ($sparePart) {
                                                $set('stok_tersedia', $sparePart->stok);
                                            }
                                        }
                                    }),

                                TextInput::make('jumlah_digunakan')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->minValue(1)
                                    ->reactive(),

                                Placeholder::make('stok_tersedia')
                                    ->label('Stok Tersedia')
                                    ->content(fn ($get) => $get('spare_part_id') 
                                        ? SparePart::find($get('spare_part_id'))?->stok ?? 0 
                                        : '-'
                                    ),

                                Textarea::make('catatan')
                                    ->label('Catatan')
                                    ->rows(2),
                            ])
                            ->columns(4)
                            ->columnSpanFull()
                            ->disabled(fn ($record) => $record && $record->status === 'completed')
                            ->defaultItems(0),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->visible(fn ($record) => $record && $record->status !== 'pending'),

                Section::make('Dokumentasi Akhir')
                    ->description('Upload foto kondisi setelah maintenance untuk menyelesaikan laporan')
                    ->schema([
                        FileUpload::make('foto_sesudah')
                            ->label('Foto Setelah Maintenance')
                            ->image()
                            ->directory('maintenance/after')
                            ->imageEditor()
                            ->required(fn ($record) => $record && $record->status === 'in_progress')
                            ->disabled(fn ($record) => $record && $record->status === 'completed'),

                        Placeholder::make('info_selesai')
                            ->label('')
                            ->content('Setelah upload foto kondisi akhir dan suku cadang, status akan berubah menjadi "Completed".')
                            ->visible(fn ($record) => $record && $record->status === 'in_progress'),
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record && $record->status !== 'pending'),
            ]);
    }
}
