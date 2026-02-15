<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

class MesinPdfController extends Controller
{
    public function download(Request $request)
    {
        $query = Mesin::query()
            ->with(['pemilik', 'komponens', 'requests'])
            ->orderBy('kode_mesin', 'asc');

        // Apply filters
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('jenis_mesin') && $request->jenis_mesin) {
            $query->where('jenis_mesin', $request->jenis_mesin);
        }

        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $mesins = $query->get();

        $html = Blade::render('pdf.mesin-list', [
            'mesins' => $mesins,
            'tanggalCetak' => now(),
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $fileName = 'Daftar_Master_Mesin_' . now()->format('Y-m-d_His') . '.pdf';

        return response()->streamDownload(
            fn () => print($dompdf->output()),
            $fileName
        );
    }

    public function downloadDetail($id)
    {
        $mesin = Mesin::with([
            'pemilik',
            'komponens',
            'requests.creator',
            'requests.approver',
            'audits.user'
        ])->findOrFail($id);

        $html = Blade::render('pdf.mesin-detail', [
            'mesin' => $mesin,
            'tanggalCetak' => now(),
        ]);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = 'Detail_Mesin_' . $mesin->kode_mesin . '_' . now()->format('Y-m-d_His') . '.pdf';

        return response()->streamDownload(
            fn () => print($dompdf->output()),
            $fileName
        );
    }
}
