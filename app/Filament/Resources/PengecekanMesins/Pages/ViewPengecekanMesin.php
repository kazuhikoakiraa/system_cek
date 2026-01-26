<?php

namespace App\Filament\Resources\PengecekanMesins\Pages;

use App\Filament\Resources\PengecekanMesins\PengecekanMesinResource;
use Filament\Resources\Pages\ViewRecord;

class ViewPengecekanMesin extends ViewRecord
{
    protected static string $resource = PengecekanMesinResource::class;

    protected static ?string $title = 'Detail Hasil Pengecekan';

    protected function getHeaderActions(): array
    {
        return [];
    }
}
