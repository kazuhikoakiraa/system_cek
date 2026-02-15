<?php

namespace App\Filament\Resources\MRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('No. Request')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-clipboard-document-list')
                    ->weight('bold'),
                    
                TextColumn::make('mesin.nama_mesin')
                    ->label('Mesin')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-cog-6-tooth')
                    ->wrap(),
                    
                TextColumn::make('komponen.nama_komponen')
                    ->label('Komponen')
                    ->searchable()
                    ->default('-')
                    ->wrap()
                    ->toggleable(),
                    
                TextColumn::make('problema_deskripsi')
                    ->label('Problema')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),
                    
                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->toggleable(),
                    
                TextColumn::make('requested_at')
                    ->label('Tgl Request')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                    
                TextColumn::make('urgency_level')
                    ->label('Urgensi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'success',
                        'medium' => 'warning',
                        'high' => 'danger',
                        'critical' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'low' => 'heroicon-o-arrow-down',
                        'medium' => 'heroicon-o-minus',
                        'high' => 'heroicon-o-arrow-up',
                        'critical' => 'heroicon-o-exclamation-triangle',
                        default => 'heroicon-o-minus',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                    
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => str_replace('_', ' ', ucwords($state, '_'))),
                    
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('urgency_level')
                    ->label('Urgensi')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ])
                    ->multiple(),
                    
                SelectFilter::make('mesin_id')
                    ->label('Mesin')
                    ->relationship('mesin', 'nama_mesin')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('requested_at', 'desc');
    }
}
