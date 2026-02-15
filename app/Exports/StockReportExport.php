<?php

namespace App\Exports;

use App\Models\SparePart;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class StockReportExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = SparePart::with('category');

        if (isset($this->filters['category_id'])) {
            $query->where('category_id', $this->filters['category_id']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('kode_suku_cadang')->get();
    }

    public function headings(): array
    {
        return [
            ['LAPORAN PERSEDIAAN SUKU CADANG'],
            ['Tanggal: ' . now()->format('d/m/Y H:i')],
            [],
            [
                'No',
                'Kode',
                'Nama Suku Cadang',
                'Kategori',
                'Stok',
                'Satuan',
                'Min',
                'Max',
                'Harga Satuan',
                'Nilai Total',
                'Lokasi',
                'Status Stok',
                'Status',
            ],
        ];
    }

    public function map($sparePart): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $sparePart->kode_suku_cadang,
            $sparePart->nama_suku_cadang,
            $sparePart->category->nama_kategori ?? '-',
            $sparePart->stok,
            $sparePart->satuan,
            $sparePart->stok_minimum,
            $sparePart->stok_maksimum,
            $sparePart->harga_satuan,
            $sparePart->nilai_stok,
            $sparePart->lokasi_penyimpanan ?? '-',
            $sparePart->status_stok,
            $sparePart->status,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2E8F0']]],
        ];
    }
}
