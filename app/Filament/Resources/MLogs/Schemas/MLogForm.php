<?php

namespace App\Filament\Resources\MLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;

class MLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Informasi Pekerjaan')
                    ->description('Detail pekerjaan maintenance')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->columns(2)
                    ->schema([
                        Select::make('m_request_id')
                            ->label('Request Maintenance')
                            ->relationship('request', 'request_number')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih request yang akan dikerjakan'),
                        
                        Select::make('teknisi_id')
                            ->label('Teknisi')
                            ->relationship('teknisi', 'name')
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
                        
                        TextInput::make('biaya_service')
                            ->label('Biaya Service (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->placeholder('0'),
                        
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
                    ])->columnSpan(2),

                Section::make('Dokumentasi')
                    ->description('Foto sebelum & sesudah')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        FileUpload::make('foto_sebelum')
                            ->label('Foto Sebelum Perbaikan')
                            ->image()
                            ->imageEditor()
                            ->directory('maintenance-logs')
                            ->maxSize(5120),
                        
                        FileUpload::make('foto_sesudah')
                            ->label('Foto Sesudah Perbaikan')
                            ->image()
                            ->imageEditor()
                            ->directory('maintenance-logs')
                            ->maxSize(5120),
                    ])->columnSpan(1),

                Section::make('Catatan Teknisi')
                    ->description('Detail pekerjaan yang dilakukan')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Textarea::make('catatan_teknisi')
                            ->label('Catatan Pekerjaan')
                            ->rows(5)
                            ->placeholder('Jelaskan pekerjaan yang dilakukan, hasil, dan temuan...')
                            ->columnSpanFull(),
                    ])->columnSpan(3),

                Section::make('Suku Cadang yang Digunakan')
                    ->description('Daftar spare parts yang dipakai dalam pekerjaan ini')
                    ->icon('heroicon-o-cube')
                    ->collapsible()
                    ->schema([
                        Repeater::make('spareParts')
                            ->label('')
                            ->relationship('spareParts')
                            ->schema([
                                Select::make('spare_part_id')
                                    ->label('Nama Suku Cadang')
                                    ->relationship('spare_part', 'nama_suku_cadang')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $sparePart = \App\Models\SparePart::find($state);
                                            if ($sparePart) {
                                                $set('harga_satuan', $sparePart->harga_satuan);
                                            }
                                        }
                                    }),
                                
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
                    ])->columnSpan(3),
            ]);
    }
}
