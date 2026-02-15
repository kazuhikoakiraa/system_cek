<?php

namespace App\Filament\Resources\MLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('m_request_id')
                    ->numeric(),
                TextEntry::make('teknisi.name')
                    ->numeric(),
                TextEntry::make('tanggal_mulai')
                    ->dateTime(),
                TextEntry::make('tanggal_selesai')
                    ->dateTime(),
                TextEntry::make('foto_sebelum'),
                TextEntry::make('foto_sesudah'),
                TextEntry::make('biaya_service')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
