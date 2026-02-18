<?php

namespace App\Observers;

use App\Models\SparePartTransaction;
use App\Models\SparePart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SparePartTransactionObserver
{
    /**
     * Handle the SparePartTransaction "creating" event.
     * Set stok sebelum dan sesudah transaksi
     */
    public function creating(SparePartTransaction $transaction): void
    {
        $sparePart = SparePart::findOrFail($transaction->spare_part_id);
        
        // Gunakan nilai stok yang sudah dikirim dari caller jika ada
        // (mis. alur maintenance yang sudah menghitung stok sebelum/sesudah).
        if ($transaction->stok_sebelum === null) {
            $transaction->stok_sebelum = $sparePart->stok;
        }

        if ($transaction->stok_sesudah === null) {
            // Calculate stok sesudah
            if (in_array($transaction->tipe_transaksi, ['IN', 'RETURN'])) {
                // Transaksi masuk: tambah stok
                $transaction->stok_sesudah = $transaction->stok_sebelum + abs($transaction->jumlah);
            } elseif ($transaction->tipe_transaksi === 'OUT') {
                // Transaksi keluar: kurangi stok
                $transaction->stok_sesudah = $transaction->stok_sebelum - abs($transaction->jumlah);
            } elseif ($transaction->tipe_transaksi === 'ADJUSTMENT') {
                // Adjustment: bisa + atau -
                $transaction->stok_sesudah = $transaction->stok_sebelum + $transaction->jumlah;
            }
        }
        
        // Set user_id jika belum diset
        if (!$transaction->user_id) {
            $transaction->user_id = Auth::id();
        }
        
        // Set tanggal transaksi jika belum diset
        if (!$transaction->tanggal_transaksi) {
            $transaction->tanggal_transaksi = now();
        }
    }

    /**
     * Handle the SparePartTransaction "created" event.
     * Update stok spare part setelah transaksi dibuat (jika approved atau auto-approve)
     */
    public function created(SparePartTransaction $transaction): void
    {
        // Auto-approve jika user adalah admin atau manager
        // Atau jika tipe transaksi adalah ADJUSTMENT (biasanya dari stock opname)
        if ($this->shouldAutoApprove($transaction)) {
            $transaction->status_approval = 'approved';
            $transaction->approved_by = Auth::id();
            $transaction->approved_at = now();
            $transaction->saveQuietly();
            
            // Update stok
            $this->updateStok($transaction);
        }
    }

    /**
     * Handle the SparePartTransaction "updated" event.
     * Update stok jika status approval berubah menjadi approved
     */
    public function updated(SparePartTransaction $transaction): void
    {
        // Jika status approval berubah menjadi approved
        if ($transaction->wasChanged('status_approval') && $transaction->status_approval === 'approved') {
            $this->updateStok($transaction);
        }
    }

    /**
     * Check apakah transaksi harus auto-approved
     */
    private function shouldAutoApprove(SparePartTransaction $transaction): bool
    {
        $user = Auth::user();
        
        // Auto-approve jika:
        // 1. User adalah admin atau manager
        // 2. Atau transaksi tipe ADJUSTMENT (dari stock opname)
        // 3. Atau transaksi tipe IN (pembelian/pengadaan)
        
        if (!$user) {
            return false;
        }
        
        if ($user->hasRole(['Super Admin', 'Admin'])) {
            return true;
        }
        
        if (in_array($transaction->tipe_transaksi, ['ADJUSTMENT', 'IN'])) {
            return true;
        }
        
        return false;
    }

    /**
     * Update stok spare part
     */
    private function updateStok(SparePartTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $sparePart = SparePart::lockForUpdate()->findOrFail($transaction->spare_part_id);
            
            // Update stok ke stok_sesudah yang sudah dihitung
            $sparePart->stok = $transaction->stok_sesudah;
            $sparePart->saveQuietly();
        });
    }
}
