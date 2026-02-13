<?php

namespace App\Exports;

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

class MaintenanceReportExcel implements FromCollection, WithHeadings, WithStyles, WithEvents, WithColumnWidths, WithCustomStartCell, WithDrawings
{
    protected Collection $data;
    protected ?Carbon $tanggalMulai;
    protected ?Carbon $tanggalSelesai;
    protected ?string $status;
    protected ?int $mesinId;

    public function __construct(
        Collection $data,
        ?Carbon $tanggalMulai = null,
        ?Carbon $tanggalSelesai = null,
        ?string $status = null,
        ?int $mesinId = null
    ) {
        $this->data = $data;
        $this->tanggalMulai = $tanggalMulai;
        $this->tanggalSelesai = $tanggalSelesai;
        $this->status = $status;
        $this->mesinId = $mesinId;
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function collection(): Collection
    {
        return $this->data->map(function ($report, $index) {
            return [
                'no' => $index + 1,
                'tanggal' => $report->created_at->format('d/m/Y H:i'),
                'mesin' => $report->mesin?->nama_mesin ?? '-',
                'komponen' => $report->komponenMesin?->nama_komponen ?? '-',
                'deskripsi_masalah' => $report->issue_description ?? '-',
                'status' => match ($report->status) {
                    'pending' => 'Menunggu',
                    'in_progress' => 'Sedang Diproses',
                    'completed' => 'Selesai',
                    default => '-'
                },
                'teknisi' => $report->teknisi?->name ?? '-',
                'tanggal_mulai' => $report->tanggal_mulai ? $report->tanggal_mulai->format('d/m/Y H:i') : '-',
                'tanggal_selesai' => $report->tanggal_selesai ? $report->tanggal_selesai->format('d/m/Y H:i') : '-',
                'spare_parts' => $report->spareParts->map(function ($sp) {
                    return $sp->nama_suku_cadang . ' (' . $sp->pivot->jumlah_digunakan . ')';
                })->join(', ') ?: '-',
                'catatan' => $report->catatan_teknisi ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Dibuat',
            'Mesin',
            'Komponen',
            'Deskripsi Masalah',
            'Status',
            'Teknisi',
            'Tanggal Mulai',
            'Tanggal Selesai',
            'Spare Parts Digunakan',
            'Catatan Teknisi',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 18,
            'C' => 20,
            'D' => 20,
            'E' => 35,
            'F' => 15,
            'G' => 20,
            'H' => 18,
            'I' => 18,
            'J' => 30,
            'K' => 30,
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
                $sheet->setCellValue('A5', 'LAPORAN MAINTENANCE');
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
                // Center alignment untuk kolom No dan Status
                $sheet->getStyle('A9:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F9:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Wrap text untuk kolom deskripsi, spare parts, dan catatan
                $sheet->getStyle('E9:E' . $lastRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('J9:J' . $lastRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('K9:K' . $lastRow)->getAlignment()->setWrapText(true);

                // ===== SUMMARY SECTION =====
                $summaryRow = $lastRow + 2;
                $sheet->setCellValue('A' . $summaryRow, 'Ringkasan:');
                $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
                
                $sheet->setCellValue('A' . ($summaryRow + 1), 'Total Laporan: ' . $this->data->count());
                
                $statusSummary = $this->data->groupBy('status')->map(fn($items) => $items->count());
                $currentRow = $summaryRow + 2;
                
                if ($statusSummary->has('pending')) {
                    $sheet->setCellValue('A' . $currentRow, '- Menunggu: ' . $statusSummary['pending']);
                    $currentRow++;
                }
                if ($statusSummary->has('in_progress')) {
                    $sheet->setCellValue('A' . $currentRow, '- Sedang Diproses: ' . $statusSummary['in_progress']);
                    $currentRow++;
                }
                if ($statusSummary->has('completed')) {
                    $sheet->setCellValue('A' . $currentRow, '- Selesai: ' . $statusSummary['completed']);
                    $currentRow++;
                }

                // Auto height untuk semua row
                foreach (range(9, $lastRow) as $row) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }
            },
        ];
    }
}
