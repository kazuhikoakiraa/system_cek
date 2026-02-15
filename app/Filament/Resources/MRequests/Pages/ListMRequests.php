<?php

namespace App\Filament\Resources\MRequests\Pages;

use App\Filament\Resources\MRequests\MRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMRequests extends ListRecords
{
    protected static string $resource = MRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
