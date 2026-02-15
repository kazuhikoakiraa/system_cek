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
                TextEntry::make('request_number'),
                TextEntry::make('mesin.id')
                    ->numeric(),
                TextEntry::make('komponen.id')
                    ->numeric(),
                TextEntry::make('created_by')
                    ->numeric(),
                TextEntry::make('requested_at')
                    ->dateTime(),
                TextEntry::make('urgency_level'),
                TextEntry::make('status'),
                TextEntry::make('approved_by')
                    ->numeric(),
                TextEntry::make('approved_at')
                    ->dateTime(),
                TextEntry::make('rejected_at')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
