<?php

namespace App\Filament\Widgets;

use App\Models\Mesin;
use App\Models\MComponent;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MachineMaintenanceAlert extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static ?string $heading = '⚠️ Mesin & Komponen Perlu Perhatian';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MComponent::query()
                    ->with(['mesin'])
                    ->where(function ($query) {
                        $query->whereNotNull('estimasi_tanggal_ganti_berikutnya')
                            ->where(function ($q) {
                                // Overdue atau dalam 30 hari ke depan
                                $q->where('estimasi_tanggal_ganti_berikutnya', '<', Carbon::now()->addDays(30));
                            });
                    })
                    ->orWhere('status_komponen', 'perlu_ganti')
                    ->orWhere('status_komponen', 'rusak')
                    ->orderByRaw("
                        CASE 
                            WHEN status_komponen = 'rusak' THEN 1
                            WHEN estimasi_tanggal_ganti_berikutnya < NOW() THEN 2
                            WHEN status_komponen = 'perlu_ganti' THEN 3
                            ELSE 4
                        END
                    ")
                    ->orderBy('estimasi_tanggal_ganti_berikutnya', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('priority')
                    ->label('')
                    ->getStateUsing(function (MComponent $record): string {
                        if ($record->status_komponen === 'rusak') {
                            return '🔴';
                        }
                        if ($record->estimasi_tanggal_ganti_berikutnya && Carbon::parse($record->estimasi_tanggal_ganti_berikutnya)->isPast()) {
                            return '🔴';
                        }
                        if ($record->status_komponen === 'perlu_ganti') {
                            return '🟡';
                        }
                        return '🟢';
                    })
                    ->sortable(false),

                Tables\Columns\TextColumn::make('mesin.kode_mesin')
                    ->label('Kode Mesin')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->url(fn (MComponent $record): string => "/admin/mesins/{$record->mesin_id}")
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('mesin.nama_mesin')
                    ->label('Nama Mesin')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('nama_komponen')
                    ->label('Komponen')
                    ->searchable()
                    ->sortable()
                    ->description(fn (MComponent $record) => $record->part_number)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status_komponen')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'normal' => 'success',
                        'perlu_ganti' => 'warning',
                        'rusak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'normal' => '✅ Normal',
                        'perlu_ganti' => '⚠️ Perlu Ganti',
                        'rusak' => '❌ Rusak',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('estimasi_tanggal_ganti_berikutnya')
                    ->label('Estimasi Ganti')
                    ->date('d M Y')
                    ->sortable()
                    ->color(function ($record) {
                        if (!$record->estimasi_tanggal_ganti_berikutnya) return 'gray';
                        $date = Carbon::parse($record->estimasi_tanggal_ganti_berikutnya);
                        if ($date->isPast()) return 'danger';
                        if ($date->diffInDays(now()) < 14) return 'warning';
                        return 'success';
                    })
                    ->description(function ($record) {
                        if (!$record->estimasi_tanggal_ganti_berikutnya) return null;
                        $date = Carbon::parse($record->estimasi_tanggal_ganti_berikutnya);
                        if ($date->isPast()) {
                            $days = $date->diffInDays(now());
                            return "⚠️ Terlambat {$days} hari";
                        }
                        $days = $date->diffInDays(now());
                        if ($days < 14) return "⏰ {$days} hari lagi";
                        return null;
                    }),

                Tables\Columns\TextColumn::make('mesin.lokasi_instalasi')
                    ->label('Lokasi')
                    ->searchable()
                    ->limit(25)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('nama_supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('harga_komponen')
                    ->label('Harga')
                    ->money('IDR')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('view_machine')
                    ->label('Lihat Mesin')
                    ->icon('heroicon-o-eye')
                    ->url(fn (MComponent $record): string => "/admin/mesins/{$record->mesin_id}")
                    ->openUrlInNewTab(),
            ])
            ->emptyStateHeading('Tidak ada komponen yang perlu perhatian')
            ->emptyStateDescription('Semua komponen dalam kondisi baik')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->defaultSort('estimasi_tanggal_ganti_berikutnya', 'asc');
    }
}
