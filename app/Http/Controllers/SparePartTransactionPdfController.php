<?php

namespace App\Http\Controllers;

use App\Models\SparePartTransaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SparePartTransactionPdfController extends Controller
{
    public function download(Request $request)
    {
        $query = SparePartTransaction::query()
            ->with(['sparePart', 'user'])
            ->orderBy('tanggal_transaksi', 'desc');

        // Apply filters
        if ($request->has('tipe_transaksi') && $request->tipe_transaksi) {
            $query->where('tipe_transaksi', $request->tipe_transaksi);
        }

        if ($request->has('spare_part_id') && $request->spare_part_id) {
            $query->where('spare_part_id', $request->spare_part_id);
        }

        if ($request->has('dari_tanggal') && $request->dari_tanggal) {
            $query->whereDate('tanggal_transaksi', '>=', $request->dari_tanggal);
        }

        if ($request->has('sampai_tanggal') && $request->sampai_tanggal) {
            $query->whereDate('tanggal_transaksi', '<=', $request->sampai_tanggal);
        }

        $transactions = $query->get();

        // Hitung statistik
        $totalMasuk = $transactions->where('tipe_transaksi', 'IN')->sum('jumlah');
        $totalKeluar = $transactions->where('tipe_transaksi', 'OUT')->sum('jumlah');
        $totalRetur = $transactions->where('tipe_transaksi', 'RETURN')->sum('jumlah');

        $pdf = Pdf::loadView('pdf.spare-part-transactions', [
            'transactions' => $transactions,
            'totalMasuk' => $totalMasuk,
            'totalKeluar' => $totalKeluar,
            'totalRetur' => $totalRetur,
            'filters' => $request->all(),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download('laporan-transaksi-suku-cadang-' . now()->format('Y-m-d') . '.pdf');
    }
}
