<?php

namespace App\Filament\Resources\PengecekanMesins\Pages;

use App\Filament\Resources\PengecekanMesins\PengecekanMesinResource;
use Filament\Resources\Pages\ViewRecord;

class EditPengecekanMesin extends ViewRecord
{
    protected static string $resource = PengecekanMesinResource::class;

    protected static ?string $title = 'Detail Pengecekan Mesin';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
