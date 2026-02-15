<?php

namespace App\Filament\Resources\MRequests\Pages;

use App\Filament\Resources\MRequests\MRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMRequest extends ViewRecord
{
    protected static string $resource = MRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
