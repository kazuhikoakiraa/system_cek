<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanPengecekanExcel implements WithMultipleSheets
{
    protected Collection $laporanData;
    protected Carbon $tanggalMulai;
    protected Carbon $tanggalSelesai;
    protected string $periode;
    protected string $nomorDokumen;

    public function __construct(
        Collection $laporanData,
        Carbon $tanggalMulai,
        Carbon $tanggalSelesai,
        string $periode,
        string $nomorDokumen
    ) {
        $this->laporanData = $laporanData;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->periode = $periode;
        $this->nomorDokumen = $nomorDokumen;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->laporanData as $mesin) {
            $sheets[] = new LaporanMesinSheet(
                $mesin,
                $this->tanggalMulai,
                $this->tanggalSelesai,
                $this->periode,
                $this->nomorDokumen
            );
        }

        return $sheets;
    }
}

class LaporanMesinSheet implements FromCollection, WithTitle, WithStyles, WithEvents, WithColumnWidths, WithCustomStartCell, WithDrawings
{
    protected $mesin;
    protected Carbon $tanggalMulai;
    protected Carbon $tanggalSelesai;
    protected string $periode;
    protected string $nomorDokumen;
    protected array $tanggalRange;
    protected int $totalDays;

    public function __construct(
        $mesin,
        Carbon $tanggalMulai,
        Carbon $tanggalSelesai,
        string $periode,
        string $nomorDokumen
    ) {
        $this->mesin = $mesin;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->periode = $periode;
        $this->nomorDokumen = $nomorDokumen;

        // Generate tanggal range
        $this->tanggalRange = [];
        $current = $tanggalMulai->copy();
        while ($current <= $tanggalSelesai) {
            $this->tanggalRange[] = $current->copy();
            $current->addDay();
        }
        $this->totalDays = count($this->tanggalRange);
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function collection(): Collection
    {
        $data = collect();

        // Header row untuk tanggal
        $headerRow = ['No', 'Item/Komponen', 'Standar', 'Frekuensi'];
        foreach ($this->tanggalRange as $tanggal) {
            $headerRow[] = $tanggal->format('d');
        }
        $headerRow[] = 'Keterangan';
        $data->push($headerRow);

        // Data komponen
        $no = 1;
        foreach ($this->mesin->komponenMesins as $komponen) {
            $row = [
                $no++,
                $komponen->nama_komponen,
                $komponen->standar ?? '-',
                ucfirst($komponen->frekuensi ?? 'harian'),
            ];

            // Cek status untuk setiap tanggal
            foreach ($this->tanggalRange as $tanggal) {
                // Skip hari Minggu - kosongkan saja
                if ($tanggal->dayOfWeek === Carbon::SUNDAY) {
                    $row[] = '';
                    continue;
                }

                $pengecekan = $this->mesin->pengecekan
                    ->first(function ($p) use ($tanggal) {
                        return $p->tanggal_pengecekan->isSameDay($tanggal);
                    });

                if ($pengecekan) {
                    $detail = $pengecekan->detailPengecekan
                        ->first(function ($d) use ($komponen) {
                            return $d->komponen_mesin_id === $komponen->id;
                        });

                    if ($detail) {
                        $row[] = match ($detail->status_sesuai) {
                            'sesuai' => '✓',
                            'tidak_sesuai' => '✗',
                            default => '-',
                        };
                    } else {
                        $row[] = '-';
                    }
                } else {
                    $row[] = '-';
                }
            }

            // Keterangan (ambil dari detail terakhir jika ada)
            $lastPengecekan = $this->mesin->pengecekan->last();
            $keterangan = '-';
            if ($lastPengecekan) {
                $lastDetail = $lastPengecekan->detailPengecekan
                    ->first(fn ($d) => $d->komponen_mesin_id === $komponen->id);
                if ($lastDetail && $lastDetail->keterangan) {
                    $keterangan = $lastDetail->keterangan;
                }
            }
            $row[] = $keterangan;

            $data->push($row);
        }

        return $data;
    }

    public function title(): string
    {
        return substr(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '_', $this->mesin->nama_mesin)), 0, 31);
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 5,  // No
            'B' => 30, // Item
            'C' => 20, // Standar
            'D' => 12, // Frekuensi
        ];

        // Kolom tanggal
        $col = 'E';
        for ($i = 0; $i < $this->totalDays; $i++) {
            $widths[$col] = 4;
            $col++;
        }

        // Kolom keterangan
        $widths[$col] = 25;

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastDataRow = 8 + $this->mesin->komponenMesins->count();
        $lastCol = $this->getColumnLetter(4 + $this->totalDays);

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
                $lastDataRow = 8 + $this->mesin->komponenMesins->count();
                $lastCol = $this->getColumnLetter(4 + $this->totalDays);

                // ===== HEADER SECTION =====
                // Row height for logo area
                $sheet->getRowDimension(1)->setRowHeight(40);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->getRowDimension(3)->setRowHeight(18);

                // Title (shifted right to make room for logo in column A)
                $sheet->setCellValue('B1', 'PT PARMA BINA ENERGI');
                $sheet->mergeCells('B1:D1');
                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);

                // Departemen
                $sheet->setCellValue('B2', 'Departemen: PRODUKSI');
                $sheet->mergeCells('B2:D2');

                // Nama Daftar Pengecekan
                $sheet->setCellValue('B3', 'Daftar Pengecekan: ' . $this->mesin->nama_mesin);
                $sheet->mergeCells('B3:D3');
                $sheet->getStyle('B3')->getFont()->setBold(true);

                // Info kanan
                $sheet->setCellValue('F1', 'No. Dokumen:');
                $sheet->setCellValue('G1', $this->nomorDokumen);
                $sheet->setCellValue('F2', 'Revisi:');
                $sheet->setCellValue('G2', '00');
                $sheet->setCellValue('F3', 'Periode:');
                $sheet->setCellValue('G3', $this->tanggalMulai->translatedFormat('F Y'));

                // Title Check Sheet
                $sheet->setCellValue('A5', 'CHECK SHEET ' . strtoupper($this->mesin->nama_mesin));
                $sheet->mergeCells('A5:' . $lastCol . '5');
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Subtitle tanggal
                $sheet->setCellValue('A6', 'Periode: ' . $this->tanggalMulai->translatedFormat('d F Y') . ' - ' . $this->tanggalSelesai->translatedFormat('d F Y'));
                $sheet->mergeCells('A6:' . $lastCol . '6');
                $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ===== BORDERS =====
                $sheet->getStyle('A8:' . $lastCol . $lastDataRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // ===== ALIGNMENT =====
                // Center alignment untuk kolom tanggal
                $startDateCol = 'E';
                $endDateCol = $this->getColumnLetter(4 + $this->totalDays - 1);
                $sheet->getStyle($startDateCol . '8:' . $endDateCol . $lastDataRow)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ===== WARNA KOLOM MINGGU (SUNDAY) =====
                $colIndex = 5; // Kolom E = kolom tanggal pertama (index 5)
                foreach ($this->tanggalRange as $tanggal) {
                    if ($tanggal->dayOfWeek === Carbon::SUNDAY) {
                        $col = $this->getColumnLetter($colIndex);
                        // Warna merah muda/pink untuk hari Minggu
                        $sheet->getStyle($col . '8:' . $col . $lastDataRow)
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('FFE6E6'); // Light red/pink
                    }
                    $colIndex++;
                }

                // ===== KETERANGAN SECTION =====
                $ketRow = $lastDataRow + 2;
                $sheet->setCellValue('A' . $ketRow, 'Keterangan:');
                $sheet->getStyle('A' . $ketRow)->getFont()->setBold(true);
                $sheet->setCellValue('A' . ($ketRow + 1), '✓ = Sesuai/OK');
                $sheet->setCellValue('A' . ($ketRow + 2), '✗ = Tidak Sesuai/NG');
                $sheet->setCellValue('A' . ($ketRow + 3), '- = Tidak ada data pengecekan/tidak dicek pada tanggal tersebut');

                // ===== TANDA TANGAN SECTION =====
                $ttdRow = $ketRow + 6;
                $sheet->setCellValue('A' . $ttdRow, 'Dibuat oleh,');
                $sheet->setCellValue('C' . $ttdRow, 'Diketahui oleh,');
                $sheet->setCellValue('E' . $ttdRow, 'Disetujui oleh,');

                $ttdSpaceRow = $ttdRow + 4;
                $sheet->setCellValue('A' . $ttdSpaceRow, '(________________)');
                $sheet->setCellValue('C' . $ttdSpaceRow, '(________________)');
                $sheet->setCellValue('E' . $ttdSpaceRow, '(________________)');

                $jabatanRow = $ttdSpaceRow + 1;
                $sheet->setCellValue('A' . $jabatanRow, 'Operator');
                $sheet->setCellValue('C' . $jabatanRow, 'Kepala Produksi');
                $sheet->setCellValue('E' . $jabatanRow, 'Manager');

                // ===== PRINT SETTINGS =====
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                // ===== ROW HEIGHT =====
                $sheet->getRowDimension(8)->setRowHeight(25);
                for ($i = 9; $i <= $lastDataRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(20);
                }
            },
        ];
    }

    public function drawings(): array
    {
        $logoPath = $this->resolveLogoPath();
        if (!$logoPath) {
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

    protected function resolveLogoPath(): ?string
    {
        $candidates = [
            public_path('images/logo.png'),
            public_path('images/logo.jpg'),
            public_path('images/logo.jpeg'),
            public_path('favicon.png'),
        ];

        foreach ($candidates as $path) {
            if (is_string($path) && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    protected function getColumnLetter(int $columnIndex): string
    {
        $letter = '';
        while ($columnIndex > 0) {
            $columnIndex--;
            $letter = chr(65 + ($columnIndex % 26)) . $letter;
            $columnIndex = (int) ($columnIndex / 26);
        }
        return $letter;
    }
}
