<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Filament\Resources\SparePartTransactionResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSparePartTransaction extends ViewRecord
{
    protected static string $resource = SparePartTransactionResource::class;

    protected static ?string $title = 'Detail Transaksi Suku Cadang';
}
