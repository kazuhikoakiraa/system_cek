<?php

namespace App\Filament\Resources\DaftarPengecekanResource\Pages;

use App\Filament\Resources\DaftarPengecekanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDaftarPengecekan extends CreateRecord
{
    protected static string $resource = DaftarPengecekanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Data daftar pengecekan berhasil ditambahkan';
    }
}
