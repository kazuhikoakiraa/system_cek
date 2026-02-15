<?php

namespace App\Exports;

use App\Models\SparePartTransaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SparePartTransactionExport implements FromCollection, WithHeadings, WithStyles, WithEvents, WithColumnWidths, WithCustomStartCell, WithDrawings
{
    protected Collection $data;
    protected ?Carbon $tanggalMulai;
    protected ?Carbon $tanggalSelesai;
    protected ?string $tipeTransaksi;
    protected ?int $sparePartId;

    public function __construct(
        Collection $data,
        ?Carbon $tanggalMulai = null,
        ?Carbon $tanggalSelesai = null,
        ?string $tipeTransaksi = null,
        ?int $sparePartId = null
    ) {
        $this->data = $data;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->tipeTransaksi = $tipeTransaksi;
        $this->sparePartId = $sparePartId;
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function collection(): Collection
    {
        return $this->data->map(function ($transaction, $index) {
            return [
                'no' => $index + 1,
                'nomor_transaksi' => $transaction->nomor_transaksi,
                'tanggal' => $transaction->tanggal_transaksi->format('d/m/Y H:i'),
                'kode' => $transaction->sparePart->kode_suku_cadang,
                'nama' => $transaction->sparePart->nama_suku_cadang,
                'tipe' => match ($transaction->tipe_transaksi) {
                    'IN' => 'Masuk',
                    'OUT' => 'Keluar',
                    'RETURN' => 'Retur',
                    default => $transaction->tipe_transaksi
                },
                'jumlah' => $transaction->jumlah . ' ' . $transaction->sparePart->satuan,
                'stok_sebelum' => $transaction->stok_sebelum,
                'stok_sesudah' => $transaction->stok_sesudah,
                'keterangan' => $transaction->keterangan ?? '-',
                'user' => $transaction->user->name ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Transaksi',
            'Tanggal & Waktu',
            'Kode',
            'Nama Suku Cadang',
            'Tipe',
            'Jumlah',
            'Stok Sebelum',
            'Stok Sesudah',
            'Keterangan',
            'Diinput Oleh',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // No. Transaksi
            'C' => 18,  // Tanggal & Waktu
            'D' => 12,  // Kode
            'E' => 25,  // Nama Suku Cadang
            'F' => 10,  // Tipe
            'G' => 12,  // Jumlah
            'H' => 12,  // Stok Sebelum
            'I' => 12,  // Stok Sesudah
            'J' => 30,  // Keterangan
            'K' => 20,  // Diinput Oleh
        ];
    }

    public function drawings(): array
    {
        $logoPath = public_path('favicon.png');
        if (!file_exists($logoPath)) {
            return [];
        }

        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Company Logo');
        $drawing->setPath($logoPath);
        $drawing->setHeight(50);
        $drawing->setCoordinates('A1');
        $drawing->setOffsetX(5);
        $drawing->setOffsetY(5);

        return [$drawing];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = 8 + $this->data->count();

        return [
            // Header row
            8 => [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = 8 + $this->data->count();

                // ===== HEADER SECTION =====
                // Row height for logo area
                $sheet->getRowDimension(1)->setRowHeight(40);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // Title (shifted right to make room for logo in column A)
                $sheet->setCellValue('B1', 'PT PARAMA BINA ENERGI');
                $sheet->mergeCells('B1:D1');
                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);

                // Departemen
                $sheet->setCellValue('B2', 'Departemen: PRODUKSI');
                $sheet->mergeCells('B2:D2');

                // Info kanan
                $sheet->setCellValue('F1', 'No. Dokumen:');
                $sheet->setCellValue('G1', '');
                $sheet->setCellValue('F2', 'Periode:');
                
                if ($this->tanggalMulai && $this->tanggalSelesai) {
                    $sheet->setCellValue('G2', $this->tanggalMulai->translatedFormat('F Y'));
                } elseif ($this->tanggalMulai) {
                    $sheet->setCellValue('G2', $this->tanggalMulai->translatedFormat('F Y'));
                } elseif ($this->tanggalSelesai) {
                    $sheet->setCellValue('G2', $this->tanggalSelesai->translatedFormat('F Y'));
                } else {
                    $sheet->setCellValue('G2', now()->translatedFormat('F Y'));
                }

                // Title Report
                $sheet->setCellValue('A5', 'LAPORAN TRANSAKSI SUKU CADANG');
                $sheet->mergeCells('A5:K5');
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Subtitle periode
                $subtitle = 'Periode: ';
                if ($this->tanggalMulai && $this->tanggalSelesai) {
                    $subtitle .= $this->tanggalMulai->translatedFormat('d F Y') . ' - ' . $this->tanggalSelesai->translatedFormat('d F Y');
                } elseif ($this->tanggalMulai) {
                    $subtitle .= 'Sejak ' . $this->tanggalMulai->translatedFormat('d F Y');
                } elseif ($this->tanggalSelesai) {
                    $subtitle .= 'Sampai ' . $this->tanggalSelesai->translatedFormat('d F Y');
                } else {
                    $subtitle .= 'Semua Data';
                }
                $sheet->setCellValue('A6', $subtitle);
                $sheet->mergeCells('A6:K6');
                $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ===== BORDERS =====
                $sheet->getStyle('A8:K' . $lastRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // ===== ALIGNMENT =====
                // Center alignment untuk kolom No, Tipe, Stok
                $sheet->getStyle('A9:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F9:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H9:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Wrap text untuk kolom keterangan
                $sheet->getStyle('J9:J' . $lastRow)->getAlignment()->setWrapText(true);

                // ===== SUMMARY SECTION =====
                $summaryRow = $lastRow + 2;
                $sheet->setCellValue('A' . $summaryRow, 'Ringkasan:');
                $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
                
                $sheet->setCellValue('A' . ($summaryRow + 1), 'Total Transaksi: ' . $this->data->count());
                
                // Hitung total per tipe
                $totalMasuk = $this->data->where('tipe_transaksi', 'IN')->sum('jumlah');
                $totalKeluar = $this->data->where('tipe_transaksi', 'OUT')->sum('jumlah');
                $totalRetur = $this->data->where('tipe_transaksi', 'RETURN')->sum('jumlah');
                
                $currentRow = $summaryRow + 2;
                $sheet->setCellValue('A' . $currentRow, '- Total Masuk: ' . number_format($totalMasuk) . ' unit');
                $currentRow++;
                $sheet->setCellValue('A' . $currentRow, '- Total Keluar: ' . number_format($totalKeluar) . ' unit');
                $currentRow++;
                $sheet->setCellValue('A' . $currentRow, '- Total Retur: ' . number_format($totalRetur) . ' unit');

                // ===== TANDA TANGAN SECTION =====
                $ttdRow = $currentRow + 4;
                
                // Lokasi dan tanggal
                $sheet->setCellValue('A' . ($ttdRow - 1), 'PEMATANGSIANTAR, ' . now()->translatedFormat('d F Y'));
                $sheet->mergeCells('A' . ($ttdRow - 1) . ':K' . ($ttdRow - 1));
                $sheet->getStyle('A' . ($ttdRow - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                $sheet->setCellValue('A' . $ttdRow, 'Dibuat oleh,');
                $sheet->setCellValue('D' . $ttdRow, 'Diketahui oleh,');
                $sheet->setCellValue('G' . $ttdRow, 'Disetujui oleh,');

                $ttdSpaceRow = $ttdRow + 4;
                $sheet->setCellValue('A' . $ttdSpaceRow, '(________________)');
                $sheet->setCellValue('D' . $ttdSpaceRow, '(________________)');
                $sheet->setCellValue('G' . $ttdSpaceRow, '(________________)');

                $jabatanRow = $ttdSpaceRow + 1;
                $sheet->setCellValue('A' . $jabatanRow, 'Staff Gudang');
                $sheet->setCellValue('D' . $jabatanRow, 'Kepala Gudang');
                $sheet->setCellValue('G' . $jabatanRow, 'Manager');

                // Auto height untuk semua row
                foreach (range(9, $lastRow) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }
            },
        ];
    }
}
