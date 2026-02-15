<?php

namespace App\Filament\Resources\MLogs\Pages;

use App\Filament\Resources\MLogs\MLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMLogs extends ListRecords
{
    protected static string $resource = MLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat Log Perawatan')
                ->icon('heroicon-o-plus'),
        ];
    }
}

