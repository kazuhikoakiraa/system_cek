<?php

namespace App\Filament\Resources\MRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('request_number')
                    ->label('Nomor Request')
                    ->badge()
                    ->color('primary'),
                TextEntry::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        default => 'gray',
                    }),
                TextEntry::make('mesin.nama_mesin')
                    ->label('Mesin')
                    ->icon('heroicon-o-cog-6-tooth'),
                TextEntry::make('komponen.nama_komponen')
                    ->label('Komponen')
                    ->default('-'),
                TextEntry::make('problema_deskripsi')
                    ->label('Deskripsi Masalah')
                    ->columnSpanFull(),
                TextEntry::make('urgency_level')
                    ->label('Tingkat Urgensi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextEntry::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->icon('heroicon-o-user'),
                TextEntry::make('requested_at')
                    ->label('Tanggal Request')
                    ->dateTime('d M Y H:i'),
                TextEntry::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i'),
                TextEntry::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i'),
            ]);
    }
}
