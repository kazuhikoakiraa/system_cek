<?php

namespace App\Filament\Resources\MesinResource\Pages;

use App\Filament\Resources\MesinResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMesin extends CreateRecord
{
    protected static string $resource = MesinResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data mesin berhasil ditambahkan';
    }
}
