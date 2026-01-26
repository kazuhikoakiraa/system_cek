<?php

namespace App\Filament\Resources\SpareParts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SparePartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Suku Cadang')
                    ->schema([
                        TextInput::make('kode_suku_cadang')
                            ->label('Kode Suku Cadang')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('nama_suku_cadang')
                            ->label('Nama Suku Cadang')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Stok & Satuan')
                    ->schema([
                        TextInput::make('stok')
                            ->label('Stok')
                            ->required()
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Select::make('satuan')
                            ->label('Satuan')
                            ->options([
                                'pcs' => 'Pcs',
                                'unit' => 'Unit',
                                'set' => 'Set',
                                'box' => 'Box',
                                'pack' => 'Pack',
                                'meter' => 'Meter',
                                'liter' => 'Liter',
                            ])
                            ->default('pcs')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
