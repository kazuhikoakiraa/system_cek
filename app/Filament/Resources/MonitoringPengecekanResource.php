<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MonitoringPengecekanResource\Pages;
use App\Models\Mesin;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MonitoringPengecekanResource extends Resource
{
    protected static ?string $model = Mesin::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Monitoring Pengecekan';

    protected static ?string $modelLabel = 'Monitoring Pengecekan';

    protected static ?string $pluralModelLabel = 'Monitoring Pengecekan';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_mesin')
                    ->label('Nama Mesin')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('operator.name')
                    ->label('Operator Bertanggung Jawab')
                    ->searchable()
                    ->sortable()
                    ->default('Tidak ada operator'),

                TextColumn::make('status_pengecekan_hari_ini')
                    ->label('Status Pengecekan Hari Ini')
                    ->badge()
                    ->state(function (Mesin $record): string {
                        $pengecekanHariIni = $record->pengecekan()
                            ->whereDate('tanggal_pengecekan', today())
                            ->first();

                        if (!$pengecekanHariIni) {
                            return 'Belum Dicek';
                        }

                        return $pengecekanHariIni->status === 'selesai' 
                            ? 'Sudah Dicek' 
                            : 'Sedang Dicek';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Sudah Dicek' => 'success',
                        'Sedang Dicek' => 'warning',
                        'Belum Dicek' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Sudah Dicek' => 'heroicon-o-check-circle',
                        'Sedang Dicek' => 'heroicon-o-clock',
                        'Belum Dicek' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->leftJoin('pengecekan_mesins', function ($join) {
                            $join->on('mesins.id', '=', 'pengecekan_mesins.mesin_id')
                                ->whereDate('pengecekan_mesins.tanggal_pengecekan', today());
                        })
                        ->orderBy('pengecekan_mesins.status', $direction)
                        ->select('mesins.*');
                    }),

                TextColumn::make('waktu_pengecekan')
                    ->label('Waktu Pengecekan')
                    ->state(function (Mesin $record): ?string {
                        $pengecekanHariIni = $record->pengecekan()
                            ->whereDate('tanggal_pengecekan', today())
                            ->first();

                        return $pengecekanHariIni?->created_at?->format('H:i:s');
                    })
                    ->placeholder('-')
                    ->alignCenter(),

                TextColumn::make('operator_pengecekan')
                    ->label('Operator Pengecekan')
                    ->state(function (Mesin $record): ?string {
                        $pengecekanHariIni = $record->pengecekan()
                            ->whereDate('tanggal_pengecekan', today())
                            ->first();

                        return $pengecekanHariIni?->operator?->name;
                    })
                    ->placeholder('-')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('pengecekan', function ($q) use ($search) {
                            $q->whereDate('tanggal_pengecekan', today())
                                ->whereHas('operator', function ($oq) use ($search) {
                                    $oq->where('name', 'like', "%{$search}%");
                                });
                        });
                    }),
            ])
            ->defaultSort('nama_mesin')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status_pengecekan')
                    ->label('Status Pengecekan')
                    ->options([
                        'sudah' => 'Sudah Dicek',
                        'sedang' => 'Sedang Dicek',
                        'belum' => 'Belum Dicek',
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
                            'belum' => $query->whereDoesntHave('pengecekan', function ($q) {
                                $q->whereDate('tanggal_pengecekan', today());
                            }),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([])
            ->bulkActions([])
            ->poll('30s'); // Auto-refresh setiap 30 detik
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoringPengecekan::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}
