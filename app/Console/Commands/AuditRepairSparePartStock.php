<?php

namespace App\Console\Commands;

use App\Models\SparePart;
use App\Models\SparePartTransaction;
use Illuminate\Console\Command;

class AuditRepairSparePartStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sparepart:audit-stock
                            {--fix : Terapkan perbaikan ke transaksi dan stok aktual}
                            {--spare-part-id= : Audit spare part tertentu berdasarkan ID}
                            {--include-pending : Sertakan transaksi pending/rejected saat audit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit konsistensi transaksi stok suku cadang dan perbaiki jika diperlukan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $applyFix = (bool) $this->option('fix');
        $sparePartId = $this->option('spare-part-id');
        $includePending = (bool) $this->option('include-pending');

        $this->info($applyFix
            ? 'Menjalankan audit + perbaikan stok suku cadang...'
            : 'Menjalankan audit stok suku cadang (dry-run)...');

        $query = SparePart::query()->orderBy('id');

        if (! empty($sparePartId)) {
            $query->where('id', $sparePartId);
        }

        $totalParts = 0;
        $partsWithIssues = 0;
        $totalTxnMismatch = 0;
        $totalStockMismatch = 0;
        $txnFixed = 0;
        $stockFixed = 0;

        $query->chunkById(100, function ($parts) use (
            $includePending,
            $applyFix,
            &$totalParts,
            &$partsWithIssues,
            &$totalTxnMismatch,
            &$totalStockMismatch,
            &$txnFixed,
            &$stockFixed
        ) {
            foreach ($parts as $part) {
                $totalParts++;

                $transactionsQuery = SparePartTransaction::query()
                    ->where('spare_part_id', $part->id)
                    ->orderBy('tanggal_transaksi')
                    ->orderBy('id');

                if (! $includePending) {
                    $transactionsQuery->where('status_approval', 'approved');
                }

                $transactions = $transactionsQuery->get();

                if ($transactions->isEmpty()) {
                    continue;
                }

                $hasIssue = false;
                $expectedBefore = null;
                $expectedFinalStock = null;

                foreach ($transactions as $transaction) {
                    if ($expectedBefore === null) {
                        $expectedBefore = (int) $transaction->stok_sebelum;
                    }

                    $expectedAfter = $expectedBefore + $this->resolveDelta($transaction);

                    $beforeMismatch = (int) $transaction->stok_sebelum !== $expectedBefore;
                    $afterMismatch = (int) $transaction->stok_sesudah !== $expectedAfter;

                    if ($beforeMismatch || $afterMismatch) {
                        $hasIssue = true;
                        $totalTxnMismatch++;

                        $this->line(
                            "[TXN MISMATCH] Part #{$part->id} {$part->nama_suku_cadang} | ".
                            "TRX #{$transaction->id} {$transaction->nomor_transaksi} | ".
                            "before {$transaction->stok_sebelum}=>{$expectedBefore}, ".
                            "after {$transaction->stok_sesudah}=>{$expectedAfter}"
                        );

                        if ($applyFix) {
                            SparePartTransaction::query()
                                ->whereKey($transaction->id)
                                ->update([
                                'stok_sebelum' => $expectedBefore,
                                'stok_sesudah' => $expectedAfter,
                                ]);
                            $txnFixed++;
                        }
                    }

                    $expectedBefore = $expectedAfter;
                    $expectedFinalStock = $expectedAfter;
                }

                if ($expectedFinalStock !== null && (int) $part->stok !== (int) $expectedFinalStock) {
                    $hasIssue = true;
                    $totalStockMismatch++;

                    $this->line(
                        "[STOK MISMATCH] Part #{$part->id} {$part->nama_suku_cadang} | ".
                        "stok master {$part->stok}=>{$expectedFinalStock}"
                    );

                    if ($applyFix) {
                        SparePart::query()
                            ->whereKey($part->id)
                            ->update(['stok' => $expectedFinalStock]);
                        $stockFixed++;
                    }
                }

                if ($hasIssue) {
                    $partsWithIssues++;
                }
            }
        });

        $this->newLine();
        $this->info("Total spare part diproses: {$totalParts}");
        $this->info("Spare part bermasalah: {$partsWithIssues}");
        $this->info("Mismatch transaksi: {$totalTxnMismatch}");
        $this->info("Mismatch stok master: {$totalStockMismatch}");

        if ($applyFix) {
            $this->info("Transaksi diperbaiki: {$txnFixed}");
            $this->info("Stok master diperbaiki: {$stockFixed}");
            $this->info('Perbaikan selesai.');
        } else {
            $this->warn('Mode audit saja. Jalankan ulang dengan --fix untuk menerapkan perbaikan.');
        }

        return self::SUCCESS;
    }

    private function resolveDelta(SparePartTransaction $transaction): int
    {
        $jumlah = (int) $transaction->jumlah;

        return match ($transaction->tipe_transaksi) {
            'IN', 'RETURN' => abs($jumlah),
            'OUT' => -abs($jumlah),
            'ADJUSTMENT' => $jumlah,
            default => 0,
        };
    }
}
