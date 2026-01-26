<?php

namespace App\Filament\Resources\PengecekanMesins\Tables;

use App\Models\Mesin;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengecekanMesinsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->query(Mesin::query())
            ->columns([
                TextColumn::make('nama_mesin')
                    ->label('Nama Mesin')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('operator.name')
                    ->label('Operator')
                    ->searchable()
                    ->sortable()
                    ->default('Tidak ada operator'),

                TextColumn::make('status_pengecekan_hari_ini')
                    ->label('Status Pengecekan')
                    ->badge()
                    ->state(function (Mesin $record): string {
                        $pengecekanHariIni = $record->pengecekan()
                            ->whereDate('tanggal_pengecekan', today())
                            ->first();

                        if (!$pengecekanHariIni) {
                            return 'Tidak Ada Data Pengecekan/Tidak Dicek';
                        }

                        return match ($pengecekanHariIni->status) {
                            'selesai' => 'Sudah Dicek',
                            'dalam_proses' => 'Sedang Dicek',
                            default => 'Unknown',
                        };
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Sudah Dicek' => 'success',
                        'Sedang Dicek' => 'warning',
                        'Tidak Ada Data Pengecekan/Tidak Dicek' => 'gray',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Sudah Dicek' => 'heroicon-o-check-circle',
                        'Sedang Dicek' => 'heroicon-o-clock',
                        'Tidak Ada Data Pengecekan/Tidak Dicek' => 'heroicon-o-minus-circle',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                TextColumn::make('waktu_pengecekan')
                    ->label('Waktu Pengecekan')
                    ->state(function (Mesin $record): ?string {
                        $pengecekanHariIni = $record->pengecekan()
                            ->whereDate('tanggal_pengecekan', today())
                            ->first();

                        return $pengecekanHariIni?->tanggal_pengecekan?->format('H:i:s');
                    })
                    ->placeholder('-')
                    ->alignCenter(),
            ])
            ->defaultSort('nama_mesin')
            ->filters([
                SelectFilter::make('status_pengecekan')
                    ->label('Status Pengecekan')
                    ->options([
                        'sudah' => 'Sudah Dicek',
                        'sedang' => 'Sedang Dicek',
                        'tidak_ada_data' => 'Tidak Ada Data Pengecekan/Tidak Dicek',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $status = $data['value'] ?? null;

                        if (!$status) {
                            return $query;
                        }

                        return match ($status) {
                            'sudah' => $query->whereHas('pengecekan', function ($q) {
                                $q->whereDate('tanggal_pengecekan', today())
                                    ->where('status', 'selesai');
                            }),
                            'sedang' => $query->whereHas('pengecekan', function ($q) {
                                $q->whereDate('tanggal_pengecekan', today())
                                    ->where('status', 'dalam_proses');
                            }),
                            'tidak_ada_data' => $query->whereDoesntHave('pengecekan', function ($q) {
                                $q->whereDate('tanggal_pengecekan', today());
                            }),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(function (Mesin $record): ?string {
                        $pengecekan = $record->pengecekan()
                            ->whereDate('tanggal_pengecekan', today())
                            ->first();
                        
                        if ($pengecekan) {
                            return route('filament.admin.resources.pengecekan-mesins.view', ['record' => $pengecekan->id]);
                        }
                        
                        return null;
                    })
                    ->visible(function (Mesin $record): bool {
                        return $record->pengecekan()
                            ->whereDate('tanggal_pengecekan', today())
                            ->exists();
                    }),
            ])
            ->poll('30s');
    }
}