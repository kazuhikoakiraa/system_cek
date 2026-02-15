<?php

namespace App\Exports;

use App\Models\Mesin;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MesinLengkapExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Mesin::with(['pemilik', 'komponens', 'requests']);

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['jenis_mesin'])) {
            $query->where('jenis_mesin', $this->filters['jenis_mesin']);
        }

        if (isset($this->filters['user_id'])) {
            $query->where('user_id', $this->filters['user_id']);
        }

        return $query->orderBy('kode_mesin')->get();
    }

    public function headings(): array
    {
        return [
            ['LAPORAN DAFTAR MASTER MESIN'],
            ['Tanggal: ' . now()->format('d/m/Y H:i')],
            [],
            [
                'No',
                'Kode Mesin',
                'Nama Mesin',
                'Serial Number',
                'Manufaktur',
                'Model',
                'Tahun Pembuatan',
                'Jenis Mesin',
                'Lokasi Instalasi',
                'Supplier',
                'Tanggal Pengadaan',
                'Harga Pengadaan',
                'No. Invoice',
                'Tanggal Garansi Berakhir',
                'Umur Ekonomis (Tahun)',
                'Status',
                'Kondisi Terakhir',
                'Penanggung Jawab',
                'Jumlah Komponen',
                'Jumlah Request',
                'Spesifikasi Teknis',
                'Catatan',
            ],
        ];
    }

    public function map($mesin): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $mesin->kode_mesin,
            $mesin->nama_mesin,
            $mesin->serial_number,
            $mesin->manufacturer,
            $mesin->model_number,
            $mesin->tahun_pembuatan,
            $mesin->jenis_mesin,
            $mesin->lokasi_instalasi,
            $mesin->supplier,
            $mesin->tanggal_pengadaan ? $mesin->tanggal_pengadaan->format('d/m/Y') : '',
            $mesin->harga_pengadaan ? 'Rp ' . number_format($mesin->harga_pengadaan, 2, ',', '.') : '',
            $mesin->nomor_invoice,
            $mesin->tanggal_waranty_expired ? $mesin->tanggal_waranty_expired->format('d/m/Y') : '',
            $mesin->umur_ekonomis_tahun,
            $mesin->status,
            $mesin->kondisi_terakhir,
            $mesin->pemilik?->name ?? '-',
            $mesin->komponens->count(),
            $mesin->requests->count(),
            $mesin->spesifikasi_teknis,
            $mesin->catatan,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'L' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
        ];
    }
}

