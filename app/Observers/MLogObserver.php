<?php

namespace App\Observers;

use App\Models\MLog;
use App\Models\MAudit;
use App\Models\SparePartTransaction;
use App\Models\User;
use App\Notifications\MaintenanceRequestApproved;
use Illuminate\Support\Facades\Notification;

class MLogObserver
{
    /**
     * Handle the MLog "created" event.
     */
    public function created(MLog $mLog): void
    {
        // Record audit trail - teknisi mulai kerja
        MAudit::create([
            'mesin_id' => $mLog->request->mesin_id ?? null,
            'm_request_id' => $mLog->m_request_id,
            'm_log_id' => $mLog->id,
            'action_type' => 'teknisi_started',
            'user_id' => $mLog->teknisi_id,
            'deskripsi_perubahan' => "Teknisi mulai pengerjaan maintenance",
            'perubahan_data' => [
                'teknisi' => $mLog->teknisi->name ?? null,
                'tanggal_mulai' => $mLog->tanggal_mulai,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Handle the MLog "updated" event.
     */
    public function updated(MLog $mLog): void
    {
        // Check if status changed to completed atau submitted
        if ($mLog->isDirty('status') && in_array($mLog->status, ['completed', 'submitted'])) {
            // Record audit trail - pekerjaan selesai
            MAudit::create([
                'mesin_id' => $mLog->request->mesin_id ?? null,
                'm_request_id' => $mLog->m_request_id,
                'm_log_id' => $mLog->id,
                'action_type' => 'teknisi_completed',
                'user_id' => $mLog->teknisi_id,
                'deskripsi_perubahan' => "Pekerjaan maintenance selesai",
                'perubahan_data' => [
                    'teknisi' => $mLog->teknisi->name ?? null,
                    'tanggal_selesai' => $mLog->tanggal_selesai,
                    'status' => $mLog->status,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Auto-deduct spare parts stock
            $this->deductSparePartsStock($mLog);

            // Update request status jika completed
            if ($mLog->status === 'completed' && $mLog->request) {
                $mLog->request->update(['status' => 'completed']);
                
                // Update status mesin kembali ke aktif
                if ($mLog->request->mesin) {
                    $mLog->request->mesin->update(['status' => 'aktif']);
                }
                
                // Notifikasi ke Admin & Super Admin bahwa pekerjaan selesai
                $admins = User::role(['Super Admin', 'admin'])->get();
                if ($admins->isNotEmpty()) {
                    Notification::send($admins, new MaintenanceRequestApproved($mLog->request));
                }
            }
        }
    }

    /**
     * Deduct spare parts stock yang digunakan
     */
    protected function deductSparePartsStock(MLog $mLog): void
    {
        foreach ($mLog->spareParts as $sparePart) {
            $jumlahDigunakan = $sparePart->pivot->jumlah_digunakan;
            $stokSebelum = $sparePart->stok;
            
            // Update stok
            $sparePart->decrement('stok', $jumlahDigunakan);
            $sparePart->refresh();

            // Create spare part transaction
            $maintenanceDescription = $mLog->request?->problema_deskripsi ?? 'Maintenance';
            SparePartTransaction::create([
                'nomor_transaksi' => 'TRX-MAINT-' . date('Ymd') . '-' . $mLog->id,
                'spare_part_id' => $sparePart->id,
                'tipe_transaksi' => 'OUT',
                'tanggal_transaksi' => now(),
                'user_id' => $mLog->teknisi_id,
                'jumlah' => $jumlahDigunakan,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $sparePart->stok,
                'reference_type' => 'App\\Models\\MLog',
                'reference_id' => $mLog->id,
                'keterangan' => "Penggunaan untuk maintenance - {$maintenanceDescription} (Log ID: {$mLog->id})",
                'status_approval' => 'approved',
            ]);

            // Record audit untuk stock deduction
            MAudit::create([
                'mesin_id' => $mLog->request->mesin_id ?? null,
                'm_request_id' => $mLog->m_request_id,
                'm_log_id' => $mLog->id,
                'action_type' => 'stock_deducted',
                'user_id' => $mLog->teknisi_id,
                'deskripsi_perubahan' => "Stok suku cadang dikurangi: {$sparePart->nama_suku_cadang}",
                'perubahan_data' => [
                    'spare_part' => $sparePart->nama_suku_cadang,
                    'jumlah_digunakan' => $jumlahDigunakan,
                    'stok_sebelum' => $stokSebelum,
                    'stok_sesudah' => $sparePart->stok,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }

    /**
     * Handle the MLog "deleted" event.
     */
    public function deleted(MLog $mLog): void
    {
        //
    }

    /**
     * Handle the MLog "restored" event.
     */
    public function restored(MLog $mLog): void
    {
        //
    }

    /**
     * Handle the MLog "force deleted" event.
     */
    public function forceDeleted(MLog $mLog): void
    {
        //
    }
}
