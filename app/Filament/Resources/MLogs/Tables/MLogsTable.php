<?php

namespace App\Filament\Resources\MLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request.request_number')
                    ->label('No. Request')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-clipboard-document-list')
                    ->weight('bold'),
                    
                TextColumn::make('request.mesin.nama_mesin')
                    ->label('Mesin')
                    ->searchable()
                    ->icon('heroicon-o-cog-6-tooth')
                    ->wrap(),
                    
                TextColumn::make('teknisi.name')
                    ->label('Teknisi')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-wrench-screwdriver'),
                    
                TextColumn::make('tanggal_mulai')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-play'),
                    
                TextColumn::make('tanggal_selesai')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->default('-')
                    ->icon('heroicon-o-flag'),
                    
                TextColumn::make('durasi')
                    ->label('Durasi')
                    ->getStateUsing(function ($record) {
                        if (!$record->tanggal_selesai) return '-';
                        $start = \Carbon\Carbon::parse($record->tanggal_mulai);
                        $end = \Carbon\Carbon::parse($record->tanggal_selesai);
                        return $start->diffForHumans($end, true);
                    })
                    ->toggleable(),
                    
                TextColumn::make('biaya_service')
                    ->label('Biaya Service')
                    ->money('IDR')
                    ->sortable()
                    ->icon('heroicon-o-banknotes')
                    ->toggleable(),
                    
                TextColumn::make('spareParts')
                    ->label('Suku Cadang')
                    ->getStateUsing(function ($record) {
                        return $record->spareParts->count();
                    })
                    ->badge()
                    ->color('info')
                    ->suffix(' item')
                    ->toggleable(),
                    
                ImageColumn::make('foto_sebelum')
                    ->label('Foto Sebelum')
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->toggleable(),
                    
                ImageColumn::make('foto_sesudah')
                    ->label('Foto Sesudah')
                    ->circular()
                    ->stacked()
                    ->limit(2)
                    ->toggleable(),
                    
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'warning',
                        'submitted' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'in_progress' => 'heroicon-o-clock',
                        'submitted' => 'heroicon-o-paper-airplane',
                        'completed' => 'heroicon-o-check-circle',
                        default => 'heroicon-o-minus',
                    })
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_'))),
                    
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'in_progress' => 'In Progress',
                        'submitted' => 'Submitted',
                        'completed' => 'Completed',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('teknisi_id')
                    ->label('Teknisi')
                    ->relationship('teknisi', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('mesin')
                    ->label('Mesin')
                    ->relationship('request.mesin', 'nama_mesin')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('complete')
                    ->label('Tandai Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status !== 'completed')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'completed',
                            'tanggal_selesai' => $record->tanggal_selesai ?? now(),
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal_mulai', 'desc');
    }
}
