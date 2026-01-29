<?php

namespace App\Filament\Resources\MaintenanceReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MaintenanceReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('mesin.nama_mesin')
                    ->label('Mesin')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('komponenMesin.nama_komponen')
                    ->label('Komponen')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('issue_description')
                    ->label('Deskripsi Masalah')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Diproses',
                        'completed' => 'Selesai',
                    })
                    ->sortable(),

                TextColumn::make('teknisi.name')
                    ->label('Teknisi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                ImageColumn::make('foto_sebelum')
                    ->label('Foto Awal')
                    ->circular()
                    ->toggleable(),

                ImageColumn::make('foto_sesudah')
                    ->label('Foto Akhir')
                    ->circular()
                    ->toggleable(),

                TextColumn::make('tanggal_mulai')
                    ->label('Tanggal Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('tanggal_selesai')
                    ->label('Tanggal Selesai')
                    ->dateTime('d M Y H:i')
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
                        'pending' => 'Menunggu',
                        'in_progress' => 'Sedang Diproses',
                        'completed' => 'Selesai',
                    ]),

                SelectFilter::make('mesin_id')
                    ->label('Mesin')
                    ->relationship('mesin', 'nama_mesin'),

                SelectFilter::make('teknisi_id')
                    ->label('Teknisi')
                    ->relationship('teknisi', 'name'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Lihat'),
                EditAction::make()
                    ->label('Proses'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus Laporan Maintenance Terpilih'),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
