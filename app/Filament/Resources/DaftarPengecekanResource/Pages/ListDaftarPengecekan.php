<?php

namespace App\Filament\Resources\DaftarPengecekanResource\Pages;

use App\Filament\Resources\DaftarPengecekanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDaftarPengecekan extends ListRecords
{
    protected static string $resource = DaftarPengecekanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Daftar Pengecekan')
                ->icon('heroicon-o-plus'),
        ];
    }
}
