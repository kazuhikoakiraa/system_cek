<?php

namespace App\Filament\Resources\MLogs\Pages;

use App\Filament\Resources\MLogs\MLogResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMLog extends ViewRecord
{
    protected static string $resource = MLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('heroicon-o-pencil'),
            DeleteAction::make()
                ->icon('heroicon-o-trash'),
        ];
    }
}
