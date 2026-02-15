<?php

namespace App\Filament\Resources\MesinResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class RequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'requests';

    protected static ?string $title = 'Riwayat Permintaan Perbaikan';

    protected static ?string $recordTitleAttribute = 'request_number';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Form akan menggunakan yang sudah ada di MRequestResource
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('request_number')
                    ->label('No. Request')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('komponen.nama_komponen')
                    ->label('Komponen')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('problema_deskripsi')
                    ->label('Deskripsi Masalah')
                    ->limit(50)
                    ->searchable()
                    ->wrap(),

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
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'medium' => 'Menengah',
                        'high' => 'Tinggi',
                        'critical' => 'Kritis',
                        default => $state,
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_approval' => 'warning',
                        'approved' => 'info',
                        'in_progress' => 'warning',
                        'completed' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_approval' => '⏳ Menunggu Approval',
                        'approved' => '✅ Disetujui',
                        'in_progress' => '🔧 Dalam Proses',
                        'completed' => '✅ Selesai',
                        'rejected' => '❌ Ditolak',
                        default => $state,
                    }),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('requested_at')
                    ->label('Tanggal Request')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Tanggal Approval')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending_approval' => 'Menunggu Approval',
                        'approved' => 'Disetujui',
                        'in_progress' => 'Dalam Proses',
                        'completed' => 'Selesai',
                        'rejected' => 'Ditolak',
                    ]),
                SelectFilter::make('urgency_level')
                    ->label('Tingkat Urgensi')
                    ->options([
                        'low' => 'Rendah',
                        'medium' => 'Menengah',
                        'high' => 'Tinggi',
                        'critical' => 'Kritis',
                    ]),
            ])
            ->defaultSort('requested_at', 'desc');
    }
}
