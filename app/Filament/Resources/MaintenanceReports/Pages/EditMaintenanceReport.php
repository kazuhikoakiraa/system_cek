<?php

namespace App\Filament\Resources\MaintenanceReports\Pages;

use App\Filament\Resources\MaintenanceReports\MaintenanceReportResource;
use App\Models\SparePart;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMaintenanceReport extends EditRecord
{
    protected static string $resource = MaintenanceReportResource::class;

    protected ?array $oldSparePartsData = null;
    
    protected ?array $sparePartsData = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil')
            ->body('Laporan maintenance berhasil diperbarui.')
            ->duration(5000)
            ->send();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load spare parts data dari pivot table
        $spareParts = $this->record->spareParts()
            ->withPivot(['jumlah_digunakan', 'catatan'])
            ->get()
            ->map(function ($sparePart) {
                return [
                    'spare_part_id' => $sparePart->id,
                    'jumlah_digunakan' => $sparePart->pivot->jumlah_digunakan,
                    'catatan' => $sparePart->pivot->catatan,
                ];
            })
            ->toArray();

        $data['spare_parts_data'] = $spareParts;
        
        // Simpan spare parts lama untuk restore stok nanti
        $this->oldSparePartsData = $spareParts;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove spare_parts_data dari data utama, akan dihandle terpisah
        $sparePartsData = $data['spare_parts_data'] ?? [];
        unset($data['spare_parts_data']);

        // Simpan spare parts data untuk diproses di afterSave
        $this->sparePartsData = $sparePartsData;

        return $data;
    }

    protected function afterSave(): void
    {
        if (isset($this->sparePartsData)) {
            // Restore stok spare parts lama
            if ($this->oldSparePartsData) {
                foreach ($this->oldSparePartsData as $oldSparePartData) {
                    if (isset($oldSparePartData['spare_part_id'])) {
                        $sparePart = SparePart::find($oldSparePartData['spare_part_id']);
                        if ($sparePart) {
                            $sparePart->increment('stok', $oldSparePartData['jumlah_digunakan'] ?? 1);
                        }
                    }
                }
            }

            // Sync spare parts dengan pivot data dan kurangi stok
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
