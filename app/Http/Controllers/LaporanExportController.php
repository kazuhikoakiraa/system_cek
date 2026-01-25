<?php

namespace App\Http\Controllers;

use App\Exports\LaporanPengecekanExcel;
use App\Models\Mesin;
use App\Models\PengecekanMesin;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanExportController extends Controller
{
    public function exportPdf(Request $request): StreamedResponse
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'mesin_id' => 'nullable|integer|exists:mesins,id',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai);
        $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
        $mesinId = $request->mesin_id;

        $laporanData = $this->getLaporanData($tanggalMulai, $tanggalSelesai, $mesinId);

        if ($laporanData->isEmpty()) {
            abort(404, 'Tidak ada data pengecekan pada periode yang dipilih.');
        }

        $nomorDokumen = $this->generateNomorDokumen();

        $pdf = Pdf::loadView('exports.laporan-pengecekan-pdf', [
            'laporanData' => $laporanData,
            'tanggalMulai' => $tanggalMulai,
            'tanggalSelesai' => $tanggalSelesai,
            'periode' => $request->filter_periode ?? 'custom',
            'nomorDokumen' => $nomorDokumen,
            'tanggalCetak' => now(),
        ])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = 'Laporan_Pengecekan_' . $tanggalMulai->format('Ymd') . '_' . $tanggalSelesai->format('Ymd') . '.pdf';

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $request->validate([
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'mesin_id' => 'nullable|integer|exists:mesins,id',
        ]);

        $tanggalMulai = Carbon::parse($request->tanggal_mulai);
        $tanggalSelesai = Carbon::parse($request->tanggal_selesai);
        $mesinId = $request->mesin_id;

        $laporanData = $this->getLaporanData($tanggalMulai, $tanggalSelesai, $mesinId);

        if ($laporanData->isEmpty()) {
            abort(404, 'Tidak ada data pengecekan pada periode yang dipilih.');
        }

        $filename = 'Laporan_Pengecekan_' . $tanggalMulai->format('Ymd') . '_' . $tanggalSelesai->format('Ymd') . '.xlsx';

        return Excel::download(
            new LaporanPengecekanExcel(
                $laporanData,
                $tanggalMulai,
                $tanggalSelesai,
                $request->filter_periode ?? 'custom',
                $this->generateNomorDokumen()
            ),
            $filename
        );
    }

    protected function getLaporanData(Carbon $tanggalMulai, Carbon $tanggalSelesai, ?int $mesinId)
    {
        $query = Mesin::with([
            'operator',
            'komponenMesins',
            'pengecekan' => function ($query) use ($tanggalMulai, $tanggalSelesai) {
                $query->whereBetween('tanggal_pengecekan', [
                    $tanggalMulai->startOfDay(),
                    $tanggalSelesai->endOfDay(),
                ])->with('detailPengecekan');
            },
        ]);

        if ($mesinId) {
            $query->where('id', $mesinId);
        }

        return $query->get();
    }

    protected function generateNomorDokumen(): string
    {
        $tahun = now()->format('Y');
        $bulan = now()->format('m');

        $sequence = PengecekanMesin::whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->count() + 1;

        return sprintf('DOC/CKMS/%s/%s/%04d', $tahun, $bulan, $sequence);
    }
}
