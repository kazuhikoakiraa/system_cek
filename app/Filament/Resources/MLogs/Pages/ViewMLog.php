<?php

namespace App\Filament\Resources\MLogs\Pages;

use App\Filament\Resources\MLogs\MLogResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMLog extends ViewRecord
{
    protected static string $resource = MLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
