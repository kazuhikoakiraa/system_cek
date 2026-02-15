<?php

namespace App\Filament\Resources\MLogs\Pages;

use App\Filament\Resources\MLogs\MLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMLog extends CreateRecord
{
    protected static string $resource = MLogResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove spare_parts_data from main data
        unset($data['spare_parts_data']);
        return $data;
    }

    protected function afterCreate(): void
    {
        // Handle spare parts pivot data
        $sparePartsData = $this->data['spare_parts_data'] ?? [];
        
        if (!empty($sparePartsData)) {
            $syncData = [];
            foreach ($sparePartsData as $sparePart) {
                if (!empty($sparePart['spare_part_id'])) {
                    $syncData[$sparePart['spare_part_id']] = [
                        'jumlah_digunakan' => $sparePart['jumlah_digunakan'] ?? 1,
                        'harga_satuan' => $sparePart['harga_satuan'] ?? 0,
                        'catatan' => $sparePart['catatan'] ?? null,
                    ];
                }
            }
            
            if (!empty($syncData)) {
                $this->record->spareParts()->sync($syncData);
            }
        }
    }
}
