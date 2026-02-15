<?php

namespace App\Filament\Resources\DaftarPengecekanResource\Pages;

use App\Filament\Resources\DaftarPengecekanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDaftarPengecekan extends EditRecord
{
    protected static string $resource = DaftarPengecekanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat'),
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Data daftar pengecekan berhasil diperbarui';
    }
}
