<?php

namespace App\Filament\Resources\PengecekanMesins\Pages;

use App\Filament\Resources\PengecekanMesins\PengecekanMesinResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPengecekanMesin extends EditRecord
{
    protected static string $resource = PengecekanMesinResource::class;

    protected static ?string $title = 'Edit Pengecekan Mesin';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
