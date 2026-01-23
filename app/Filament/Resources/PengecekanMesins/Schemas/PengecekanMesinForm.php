<?php

namespace App\Filament\Resources\PengecekanMesins\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PengecekanMesinForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengecekan')
                    ->schema([
                        Select::make('mesin_id')
                            ->label('Mesin')
                            ->relationship('mesin', 'nama_mesin')
                            ->required()
                            ->searchable()
                            ->disabled(fn ($record) => $record !== null),

                        Select::make('user_id')
                            ->label('Operator')
                            ->relationship('operator', 'name')
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        DatePicker::make('tanggal_pengecekan')
                            ->label('Tanggal Pengecekan')
                            ->required()
                            ->disabled(fn ($record) => $record !== null),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'selesai' => 'Selesai',
                                'dalam_proses' => 'Dalam Proses',
                            ])
                            ->required()
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('Detail Pengecekan Komponen')
                    ->schema([
                        Repeater::make('detailPengecekan')
                            ->label('Detail Komponen')
                            ->relationship('detailPengecekan')
                            ->schema([
                                Select::make('komponen_mesin_id')
                                    ->label('Komponen')
                                    ->relationship('komponenMesin', 'nama_komponen')
                                    ->required()
                                    ->disabled(),

                                Placeholder::make('standar')
                                    ->label('Standar')
                                    ->content(function ($get, $record) {
                                        if ($record && $record->komponenMesin) {
                                            return $record->komponenMesin->standar;
                                        }
                                        return '-';
                                    }),

                                Select::make('status_sesuai')
                                    ->label('Status')
                                    ->options([
                                        'sesuai' => 'Sesuai',
                                        'tidak_sesuai' => 'Tidak Sesuai',
                                    ])
                                    ->required()
                                    ->disabled(),

                                Textarea::make('keterangan')
                                    ->label('Keterangan')
                                    ->rows(2)
                                    ->disabled()
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record !== null),
            ]);
    }
}
