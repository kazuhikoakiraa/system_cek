<?php

namespace App\Filament\Resources\SpareParts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SparePartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_suku_cadang')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_suku_cadang')
                    ->label('Nama Suku Cadang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stok')
                    ->label('Stok')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state) => $state <= 10 ? 'danger' : ($state <= 30 ? 'warning' : 'success')),

                TextColumn::make('satuan')
                    ->label('Satuan')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
