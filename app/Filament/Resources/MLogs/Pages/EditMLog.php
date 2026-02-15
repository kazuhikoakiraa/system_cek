<?php

namespace App\Filament\Resources\MLogs\Pages;

use App\Filament\Resources\MLogs\MLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMLog extends EditRecord
{
    protected static string $resource = MLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load spare parts pivot data
        $sparePartsData = [];
        foreach ($this->record->spareParts as $sparePart) {
            $sparePartsData[] = [
                'spare_part_id' => $sparePart->id,
                'jumlah_digunakan' => $sparePart->pivot->jumlah_digunakan,
                'harga_satuan' => $sparePart->pivot->harga_satuan,
                'catatan' => $sparePart->pivot->catatan,
            ];
        }
        $data['spare_parts_data'] = $sparePartsData;
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove spare_parts_data from main data
        unset($data['spare_parts_data']);
        return $data;
    }

    protected function afterSave(): void
    {
        // Handle spare parts pivot data
        $sparePartsData = $this->data['spare_parts_data'] ?? [];
        
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
        
        $this->record->spareParts()->sync($syncData);
    }
}
