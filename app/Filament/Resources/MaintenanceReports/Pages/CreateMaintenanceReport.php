<?php

namespace App\Filament\Resources\MaintenanceReports\Pages;

use App\Filament\Resources\MaintenanceReports\MaintenanceReportResource;
use App\Models\SparePart;
use App\Models\SparePartTransaction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        if (isset($this->sparePartsData) && !empty($this->sparePartsData)) {
            // Load relasi yang dibutuhkan
            $this->record->load(['mesin', 'komponenMesin', 'teknisi']);
            
            DB::transaction(function () {
                $syncData = [];
                
                foreach ($this->sparePartsData as $sparePartData) {
                    if (isset($sparePartData['spare_part_id'])) {
                        $jumlahDigunakan = $sparePartData['jumlah_digunakan'] ?? 1;
                        
                        $syncData[$sparePartData['spare_part_id']] = [
                            'jumlah_digunakan' => $jumlahDigunakan,
                            'catatan' => $sparePartData['catatan'] ?? null,
                        ];

                        // Kurangi stok spare part dan catat transaksi
                        $sparePart = SparePart::find($sparePartData['spare_part_id']);
                        if ($sparePart) {
                            if ($sparePart->stok >= $jumlahDigunakan) {
                                $stokSebelum = $sparePart->stok;
                                $sparePart->decrement('stok', $jumlahDigunakan);
                                $sparePart->refresh();
                                $stokSesudah = $sparePart->stok;

                                // Buat informasi keterangan yang jelas
                                $mesinNama = $this->record->mesin->nama_mesin ?? '-';
                                $komponenNama = $this->record->komponenMesin->nama_komponen ?? '-';
                                $teknisiNama = $this->record->teknisi->name ?? '-';
                                $issueDesc = $this->record->issue_description ?? '-';
                                
                                $keterangan = "Digunakan untuk maintenance {$mesinNama}";
                                if ($komponenNama !== '-') {
                                    $keterangan .= " - {$komponenNama}";
                                }
                                $keterangan .= ". Masalah: {$issueDesc}. Teknisi: {$teknisiNama}";
                                
                                if (!empty($sparePartData['catatan'])) {
                                    $keterangan .= ". Catatan: {$sparePartData['catatan']}";
                                }

                                // Catat transaksi keluar
                                SparePartTransaction::create([
                                    'spare_part_id' => $sparePart->id,
                                    'tipe_transaksi' => 'OUT',
                                    'tanggal_transaksi' => $this->record->tanggal_mulai ?? now(),
                                    'user_id' => Auth::id(),
                                    'jumlah' => $jumlahDigunakan,
                                    'stok_sebelum' => $stokSebelum,
                                    'stok_sesudah' => $stokSesudah,
                                    'reference_type' => 'App\Models\MaintenanceReport',
                                    'reference_id' => $this->record->id,
                                    'keterangan' => $keterangan,
                                    'status_approval' => 'approved',
                                    'approved_by' => Auth::id(),
                                    'approved_at' => now(),
                                ]);

                                Notification::make()
                                    ->success()
                                    ->title('Transaksi Tercatat')
                                    ->body("Penggunaan {$jumlahDigunakan} {$sparePart->satuan} {$sparePart->nama_suku_cadang} telah tercatat.")
                                    ->duration(3000)
                                    ->send();

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
            });
        }
    }
}
