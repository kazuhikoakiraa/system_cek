<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Filament\Resources\SparePartTransactionResource;
use Filament\Resources\Pages\EditRecord;

class EditSparePartTransaction extends EditRecord
{
    protected static string $resource = SparePartTransactionResource::class;

    protected static ?string $title = 'Ubah Transaksi Suku Cadang';

    protected function getRedirectUrl(): ?string
    {
        return $this->getResource()::getUrl('index');
    }
}
