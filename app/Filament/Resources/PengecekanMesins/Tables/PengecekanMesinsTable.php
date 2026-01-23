<?php

namespace App\Filament\Resources\PengecekanMesins\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PengecekanMesinsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mesin.nama_mesin')
                    ->label('Nama Mesin')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tanggal_pengecekan')
                    ->label('Tanggal Pengecekan')
                    ->dateTime('d F Y H:i:s')
                    ->sortable(),

                TextColumn::make('operator.name')
                    ->label('Operator')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'selesai' => 'success',
                        'dalam_proses' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'selesai' => 'Selesai',
                        'dalam_proses' => 'Dalam Proses',
                        default => $state,
                    }),

                TextColumn::make('detailPengecekan_count')
                    ->label('Jumlah Komponen')
                    ->counts('detailPengecekan')
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'selesai' => 'Selesai',
                        'dalam_proses' => 'Dalam Proses',
                    ]),
            ])
            ->defaultSort('tanggal_pengecekan', 'desc')
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
