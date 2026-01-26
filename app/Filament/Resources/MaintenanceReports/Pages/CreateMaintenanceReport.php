<?php

namespace App\Filament\Resources\MaintenanceReports\Pages;

use App\Filament\Resources\MaintenanceReports\MaintenanceReportResource;
use App\Models\SparePart;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenanceReport extends CreateRecord
{
    protected static string $resource = MaintenanceReportResource::class;
    
    protected array $sparePartsData = [];

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Laporan maintenance berhasil dibuat.')
            ->duration(5000)
            ->send();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove spare_parts_data dari data utama
        $sparePartsData = $data['spare_parts_data'] ?? [];
        unset($data['spare_parts_data']);

        // Simpan untuk diproses di afterCreate
        $this->sparePartsData = $sparePartsData;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Attach spare parts dengan pivot data
        if (isset($this->sparePartsData)) {
            $syncData = [];
            foreach ($this->sparePartsData as $sparePartData) {
                if (isset($sparePartData['spare_part_id'])) {
                    $jumlahDigunakan = $sparePartData['jumlah_digunakan'] ?? 1;
                    
                    $syncData[$sparePartData['spare_part_id']] = [
                        'jumlah_digunakan' => $jumlahDigunakan,
                        'catatan' => $sparePartData['catatan'] ?? null,
                    ];

                    // Kurangi stok spare part
                    $sparePart = SparePart::find($sparePartData['spare_part_id']);
                    if ($sparePart) {
                        if ($sparePart->stok >= $jumlahDigunakan) {
                            $sparePart->decrement('stok', $jumlahDigunakan);
                        } else {
                            Notification::make()
                                ->warning()
                                ->title('Stok Tidak Cukup')
                                ->body("Stok {$sparePart->nama_suku_cadang} tidak mencukupi. Stok tersedia: {$sparePart->stok}")
                                ->duration(5000)
                                ->send();
                        }
                    }
                }
            }

            $this->record->spareParts()->sync($syncData);
        }
    }
}
