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
    protected function parseDateInput(?string $value): ?Carbon
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $value)) {
            return Carbon::createFromFormat('d/m/Y', $value);
        }

        return Carbon::parse($value);
    }

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

        $tanggalMulai = $this->parseDateInput($request->tanggal_mulai);
        $tanggalSelesai = $this->parseDateInput($request->tanggal_selesai);

        if ($tanggalMulai) {
            $query->whereDate('tanggal_transaksi', '>=', $tanggalMulai->toDateString());
        }

        if ($tanggalSelesai) {
            $query->whereDate('tanggal_transaksi', '<=', $tanggalSelesai->toDateString());
        }

        $transactions = $query->get();

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
