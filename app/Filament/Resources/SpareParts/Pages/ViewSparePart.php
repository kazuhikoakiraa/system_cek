<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Filament\Resources\SpareParts\RelationManagers\TransactionsRelationManager;
use App\Filament\Resources\SpareParts\SparePartResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSparePart extends ViewRecord
{
    protected static string $resource = SparePartResource::class;

    public function getRelationManagers(): array
    {
        return [
            TransactionsRelationManager::class,
        ];
    }
}
