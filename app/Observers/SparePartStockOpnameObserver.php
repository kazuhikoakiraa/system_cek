<?php

namespace App\Observers;

use App\Models\SparePartStockOpname;
use App\Models\SparePartTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SparePartStockOpnameObserver
{
    /**
     * Handle the SparePartStockOpname "updating" event.
     * Ketika stock opname di-approve, buat transaksi adjustment untuk setiap selisih
     */
    public function updating(SparePartStockOpname $opname): void
    {
        // Jika status berubah dari completed menjadi approved
        if ($opname->isDirty('status') && $opname->status === 'approved' && $opname->getOriginal('status') === 'completed') {
            $this->createAdjustmentTransactions($opname);
        }
        
        // Set approved_at dan approved_by
        if ($opname->isDirty('status') && $opname->status === 'approved') {
            $opname->approved_by = Auth::id();
            $opname->approved_at = now();
        }
        
        // Set completed_at
        if ($opname->isDirty('status') && $opname->status === 'completed') {
            $opname->completed_at = now();
        }
    }

    /**
     * Buat transaksi adjustment untuk setiap detail yang ada selisih
     */
    private function createAdjustmentTransactions(SparePartStockOpname $opname): void
    {
        DB::transaction(function () use ($opname) {
            foreach ($opname->details as $detail) {
                // Skip jika tidak ada selisih
                if ($detail->selisih == 0) {
                    continue;
                }
                
                // Buat transaksi adjustment
                SparePartTransaction::create([
                    'spare_part_id' => $detail->spare_part_id,
                    'tipe_transaksi' => 'ADJUSTMENT',
                    'tanggal_transaksi' => $opname->tanggal_opname,
                    'user_id' => $opname->user_id,
                    'jumlah' => $detail->selisih, // + jika lebih, - jika kurang
                    'reference_type' => SparePartStockOpname::class,
                    'reference_id' => $opname->id,
                    'keterangan' => "Adjustment dari Stock Opname: {$opname->nomor_opname}. " . ($detail->keterangan ?? ''),
                    'status_approval' => 'approved', // Auto-approved karena sudah melalui approval stock opname
                    'approved_by' => $opname->approved_by,
                    'approved_at' => $opname->approved_at,
                ]);
            }
        });
    }
}
