<?php

namespace App\Http\Controllers;

use App\Models\SparePartTransaction;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

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

        if ($request->has('tanggal_mulai') && $request->tanggal_mulai) {
            $query->whereDate('tanggal_transaksi', '>=', $request->tanggal_mulai);
        }

        if ($request->has('tanggal_selesai') && $request->tanggal_selesai) {
            $query->whereDate('tanggal_transaksi', '<=', $request->tanggal_selesai);
        }

        $transactions = $query->get();

        $tanggalMulai = $request->tanggal_mulai ? Carbon::parse($request->tanggal_mulai) : null;
        $tanggalSelesai = $request->tanggal_selesai ? Carbon::parse($request->tanggal_selesai) : null;

        $html = Blade::render('pdf.spare-part-transactions', [
            'transactions' => $transactions,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'Laporan_Transaksi_Suku_Cadang_' . now()->format('Y-m-d_His') . '.pdf';

        return response()->streamDownload(
            fn () => print($dompdf->output()),
            $fileName
        );
    }
}
