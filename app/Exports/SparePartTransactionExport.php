<?php

namespace App\Exports;

use App\Models\SparePartTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SparePartTransactionExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = SparePartTransaction::query()
            ->with(['sparePart', 'user'])
            ->orderBy('tanggal_transaksi', 'desc');

        // Apply filters
        if (isset($this->filters['tipe_transaksi']) && $this->filters['tipe_transaksi']) {
            $query->where('tipe_transaksi', $this->filters['tipe_transaksi']);
        }

        if (isset($this->filters['spare_part_id']) && $this->filters['spare_part_id']) {
            $query->where('spare_part_id', $this->filters['spare_part_id']);
        }

        if (isset($this->filters['dari_tanggal']) && $this->filters['dari_tanggal']) {
            $query->whereDate('tanggal_transaksi', '>=', $this->filters['dari_tanggal']);
        }

        if (isset($this->filters['sampai_tanggal']) && $this->filters['sampai_tanggal']) {
            $query->whereDate('tanggal_transaksi', '<=', $this->filters['sampai_tanggal']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No. Transaksi',
            'Tanggal & Waktu',
            'Kode Suku Cadang',
            'Nama Suku Cadang',
            'Tipe',
            'Jumlah',
            'Satuan',
            'Stok Sebelum',
            'Stok Sesudah',
            'Keterangan',
            'Diinput Oleh',
        ];
    }

    public function map($transaction): array
    {
        $tipe = match($transaction->tipe_transaksi) {
            'IN' => 'Masuk',
            'OUT' => 'Keluar',
            'RETURN' => 'Retur',
            default => $transaction->tipe_transaksi
        };

        return [
            $transaction->nomor_transaksi,
            $transaction->tanggal_transaksi->format('d/m/Y H:i'),
            $transaction->sparePart->kode_suku_cadang,
            $transaction->sparePart->nama_suku_cadang,
            $tipe,
            $transaction->jumlah,
            $transaction->sparePart->satuan,
            $transaction->stok_sebelum,
            $transaction->stok_sesudah,
            $transaction->keterangan ?? '-',
            $transaction->user->name ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function title(): string
    {
        return 'Transaksi Suku Cadang';
    }
}
