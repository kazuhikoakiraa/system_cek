<?php

namespace App\Filament\Widgets;

use App\Models\MRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentMaintenanceActivity extends BaseWidget
{
    protected static ?int $sort = 9;

    protected static ?string $heading = 'Aktivitas Maintenance Terbaru';

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                MRequest::query()
                    ->with(['mesin', 'creator'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->since()
                    ->description(fn (MRequest $record): string => $record->created_at->diffForHumans()),

                Tables\Columns\TextColumn::make('request_number')
                    ->label('No. Request')
                    ->searchable()
                    ->weight('bold')
                    ->url(fn (MRequest $record): string => "/admin/m-requests/{$record->id}")
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('mesin.nama_mesin')
                    ->label('Mesin')
                    ->searchable()
                    ->limit(30)
                    ->url(fn (MRequest $record): string => "/admin/mesins/{$record->mesin_id}")
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('problema_deskripsi')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\TextColumn::make('urgency_level')
                    ->label('Urgensi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'critical' => 'Critical',
                        'high' => 'High',
                        'medium' => 'Medium',
                        'low' => 'Low',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
