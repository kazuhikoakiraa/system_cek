<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Filament\Resources\SparePartTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSparePartTransaction extends CreateRecord
{
    protected static string $resource = SparePartTransactionResource::class;

    protected static ?string $title = 'Tambah Transaksi Suku Cadang';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
