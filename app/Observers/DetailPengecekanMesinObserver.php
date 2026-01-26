<?php

namespace App\Observers;

use App\Models\DetailPengecekanMesin;
use App\Models\MaintenanceReport;

class DetailPengecekanMesinObserver
{
    /**
     * Handle the DetailPengecekanMesin "created" event.
     */
    public function created(DetailPengecekanMesin $detailPengecekanMesin): void
    {
        $this->checkAndCreateMaintenanceReport($detailPengecekanMesin);
    }

    /**
     * Handle the DetailPengecekanMesin "updated" event.
     */
    public function updated(DetailPengecekanMesin $detailPengecekanMesin): void
    {
        // Jika status_sesuai berubah menjadi 'tidak_sesuai'
        if ($detailPengecekanMesin->isDirty('status_sesuai') && $detailPengecekanMesin->status_sesuai === 'tidak_sesuai') {
            $this->checkAndCreateMaintenanceReport($detailPengecekanMesin);
        }
    }

    /**
     * Handle the DetailPengecekanMesin "deleted" event.
     */
    public function deleted(DetailPengecekanMesin $detailPengecekanMesin): void
    {
        //
    }

    /**
     * Handle the DetailPengecekanMesin "restored" event.
     */
    public function restored(DetailPengecekanMesin $detailPengecekanMesin): void
    {
        //
    }

    /**
     * Handle the DetailPengecekanMesin "force deleted" event.
     */
    public function forceDeleted(DetailPengecekanMesin $detailPengecekanMesin): void
    {
        //
    }

    /**
     * Check if maintenance report should be created
     */
    private function checkAndCreateMaintenanceReport(DetailPengecekanMesin $detailPengecekanMesin): void
    {
        // Hanya create jika status_sesuai = 'tidak_sesuai'
        if ($detailPengecekanMesin->status_sesuai === 'tidak_sesuai') {
            // Cek apakah sudah ada maintenance report untuk detail pengecekan ini
            $existingReport = MaintenanceReport::where('detail_pengecekan_mesin_id', $detailPengecekanMesin->id)
                ->where('status', '!=', 'completed')
                ->first();

            // Jika belum ada, create baru
            if (!$existingReport) {
                $pengecekanMesin = $detailPengecekanMesin->pengecekanMesin;
                $komponenMesin = $detailPengecekanMesin->komponenMesin;

                MaintenanceReport::create([
                    'detail_pengecekan_mesin_id' => $detailPengecekanMesin->id,
                    'mesin_id' => $pengecekanMesin->mesin_id,
                    'komponen_mesin_id' => $detailPengecekanMesin->komponen_mesin_id,
                    'issue_description' => $detailPengecekanMesin->keterangan ?? "Ketidaksesuaian ditemukan pada komponen: {$komponenMesin->nama_komponen}",
                    'status' => 'pending',
                ]);
            }
        }
    }
}
