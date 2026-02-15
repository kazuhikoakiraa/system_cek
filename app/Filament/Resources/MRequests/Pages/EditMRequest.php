<?php

namespace App\Filament\Resources\MRequests\Pages;

use App\Filament\Resources\MRequests\MRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMRequest extends EditRecord
{
    protected static string $resource = MRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
