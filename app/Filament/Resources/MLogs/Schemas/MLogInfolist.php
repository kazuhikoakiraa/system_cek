<?php

namespace App\Filament\Resources\MLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;

class MLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Request')
                    ->description('Detail permintaan maintenance')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('request.request_number')
                                    ->label('No. Request')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'in_progress' => 'warning',
                                        'submitted' => 'info',
                                        'completed' => 'success',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_'))),
                                TextEntry::make('teknisi.name')
                                    ->label('Teknisi')
                                    ->icon('heroicon-o-wrench-screwdriver'),
                            ]),
                    ]),

                Section::make('Detail Mesin & Jadwal')
                    ->description('Informasi mesin dan waktu pengerjaan')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('request.mesin.nama_mesin')
                                    ->label('Mesin')
                                    ->icon('heroicon-o-cog-6-tooth'),
                                TextEntry::make('tanggal_mulai')
                                    ->label('Tanggal Mulai')
                                    ->dateTime('d M Y H:i')
                                    ->icon('heroicon-o-play'),
                                TextEntry::make('tanggal_selesai')
                                    ->label('Tanggal Selesai')
                                    ->dateTime('d M Y H:i')
                                    ->placeholder('Belum selesai')
                                    ->icon('heroicon-o-flag'),
                            ]),
                    ]),

                Section::make('Suku Cadang yang Digunakan')
                    ->description('Daftar spare parts yang dipakai')
                    ->icon('heroicon-o-cube')
                    ->schema([
                        RepeatableEntry::make('spareParts')
                            ->label('')
                            ->schema([
                                TextEntry::make('nama_suku_cadang')
                                    ->label('Nama Suku Cadang')
                                    ->weight('bold'),
                                TextEntry::make('pivot.jumlah_digunakan')
                                    ->label('Jumlah')
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('pivot.harga_satuan')
                                    ->label('Harga Satuan')
                                    ->money('IDR'),
                                TextEntry::make('pivot.catatan')
                                    ->label('Catatan')
                                    ->placeholder('Tidak ada catatan')
                                    ->columnSpan(4),
                            ])
                            ->columns(4)
                            ->contained(false)
                            ->columnSpanFull(),
                    ])
                    ->collapsed(false)
                    ->visible(fn ($record) => $record->spareParts()->count() > 0),

                Section::make('Catatan Pekerjaan')
                    ->description('Catatan dari teknisi')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('catatan_teknisi')
                            ->label('Catatan Teknisi')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada catatan'),
                    ])
                    ->collapsed(false),

                Section::make('Dokumentasi')
                    ->description('Foto sebelum dan sesudah perbaikan')
                    ->icon('heroicon-o-camera')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                ImageEntry::make('foto_sebelum')
                                    ->label('Foto Sebelum')
                                    ->height(250),
                                ImageEntry::make('foto_sesudah')
                                    ->label('Foto Sesudah')
                                    ->height(250),
                            ]),
                    ])
                    ->collapsed(false),

                Section::make('Informasi Sistem')
                    ->description('Timestamp dan audit trail')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d M Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime('d M Y H:i'),
                            ]),
                    ])
                    ->collapsed(true),
            ]);
    }
}
