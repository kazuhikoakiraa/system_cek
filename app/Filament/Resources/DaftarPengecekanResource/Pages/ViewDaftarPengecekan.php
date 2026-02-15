<?php

namespace App\Filament\Resources\DaftarPengecekanResource\Pages;

use App\Filament\Resources\DaftarPengecekanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDaftarPengecekan extends ViewRecord
{
    protected static string $resource = DaftarPengecekanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
