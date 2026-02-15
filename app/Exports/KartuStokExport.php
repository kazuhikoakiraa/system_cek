<?php

namespace App\Exports;

use App\Models\SparePartTransaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class KartuStokExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize
{
    protected $sparePartId;
    protected $startDate;
    protected $endDate;
    protected $sparePart;

    public function __construct($sparePartId, $startDate = null, $endDate = null)
    {
        $this->sparePartId = $sparePartId;
        $this->startDate = $startDate ?? now()->startOfYear();
        $this->endDate = $endDate ?? now();
        $this->sparePart = \App\Models\SparePart::find($sparePartId);
    }

    public function collection()
    {
        return SparePartTransaction::where('spare_part_id', $this->sparePartId)
            ->whereBetween('tanggal_transaksi', [$this->startDate, $this->endDate])
            ->with(['user', 'approver'])
            ->orderBy('tanggal_transaksi')
            ->orderBy('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            ['KARTU STOK SUKU CADANG'],
            ['Kode: ' . $this->sparePart->kode_suku_cadang, 'Nama: ' . $this->sparePart->nama_suku_cadang],
            ['Periode: ' . date('d/m/Y', strtotime($this->startDate)) . ' s/d ' . date('d/m/Y', strtotime($this->endDate))],
            [],
            [
                'No',
                'Tanggal',
                'Nomor Transaksi',
                'Tipe',
                'Masuk',
                'Keluar',
                'Saldo',
                'Keterangan',
                'User',
                'Status',
            ],
        ];
    }

    public function map($transaction): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $transaction->tanggal_transaksi->format('d/m/Y H:i'),
            $transaction->nomor_transaksi,
            $transaction->tipe_transaksi,
            $transaction->isIncoming() ? abs($transaction->jumlah) : '',
            $transaction->isOutgoing() ? abs($transaction->jumlah) : '',
            $transaction->stok_sesudah,
            $transaction->keterangan ?? '-',
            $transaction->user->name ?? '-',
            $transaction->status_approval === 'approved' ? 'Approved' : 'Pending',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2E8F0']]],
        ];
    }
}
