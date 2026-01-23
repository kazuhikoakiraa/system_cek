<?php

namespace App\Filament\Resources\MesinResource\Pages;

use App\Filament\Resources\MesinResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewMesin extends ViewRecord
{
    protected static string $resource = MesinResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
