<?php

namespace App\Exports;

use App\Models\Mesin;
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
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class MesinLengkapExport implements WithMultipleSheets
{
    protected Collection $mesins;

    public function __construct($filters = [])
    {
        $query = Mesin::with(['pemilik', 'komponens', 'requests']);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['jenis_mesin'])) {
            $query->where('jenis_mesin', $filters['jenis_mesin']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $this->mesins = $query->orderBy('kode_mesin')->get();
    }

    public function sheets(): array
    {
        $sheets = [];

        // Summary sheet
        $sheets[] = new MesinSummarySheet($this->mesins);

        // Detail sheet untuk setiap mesin
        foreach ($this->mesins as $mesin) {
            $sheets[] = new MesinDetailSheet($mesin);
        }

        return $sheets;
    }
}

class MesinSummarySheet implements FromCollection, WithTitle, WithStyles, WithColumnWidths, WithEvents, WithCustomStartCell, WithDrawings
{
    protected Collection $mesins;

    public function __construct(Collection $mesins)
    {
        $this->mesins = $mesins;
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function collection(): Collection
    {
        $data = collect([
            [
                'No',
                'Kode Mesin',
                'Nama Mesin',
                'Serial Number',
                'Manufacturer',
                'Model',
                'Jenis Mesin',
                'Lokasi',
                'Status',
                'Jumlah Komponen',
                'Jumlah Maintenance',
            ],
        ]);

        foreach ($this->mesins as $index => $mesin) {
            $data->push([
                $index + 1,
                $mesin->kode_mesin,
                $mesin->nama_mesin,
                $mesin->serial_number ?? '-',
                $mesin->manufacturer ?? '-',
                $mesin->model_number ?? '-',
                $mesin->jenis_mesin ?? '-',
                $mesin->lokasi_instalasi ?? '-',
                ucfirst($mesin->status ?? '-'),
                $mesin->komponens->count(),
                $mesin->requests->count(),
            ]);
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Table header
            8 => [
                'font' => ['bold' => true, 'size' => 10],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 12,
            'C' => 20,
            'D' => 18,
            'E' => 18,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 12,
            'J' => 15,
            'K' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = 8 + $this->mesins->count();

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
                $sheet->setCellValue('G2', now()->translatedFormat('F Y'));

                // Title Report
                $sheet->setCellValue('A5', 'LAPORAN DAFTAR MASTER MESIN');
                $sheet->mergeCells('A5:K5');
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Subtitle tanggal
                $sheet->setCellValue('A6', 'Tanggal: ' . now()->translatedFormat('d F Y H:i'));
                $sheet->mergeCells('A6:K6');
                $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ===== BORDERS =====
                $sheet->getStyle('A8:K' . $lastDataRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

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

class MesinDetailSheet implements FromCollection, WithTitle, WithStyles, WithEvents, WithColumnWidths, WithCustomStartCell, WithDrawings
{
    protected $mesin;

    public function __construct($mesin)
    {
        $this->mesin = $mesin;
    }

    public function title(): string
    {
        return substr(preg_replace('/[^a-zA-Z0-9]/', '', $this->mesin->kode_mesin), 0, 31);
    }

    public function startCell(): string
    {
        return 'A8';
    }

    public function collection(): Collection
    {
        $data = collect();

        // Data Mesin - format 2 kolom (Label | Value)
        $data->push(['DATA MESIN', '']);
        $data->push(['Kode Mesin', $this->mesin->kode_mesin]);
        $data->push(['Nama Mesin', $this->mesin->nama_mesin]);
        $data->push(['Serial Number', $this->mesin->serial_number ?? '-']);
        $data->push(['Manufacturer', $this->mesin->manufacturer ?? '-']);
        $data->push(['Model Number', $this->mesin->model_number ?? '-']);
        $data->push(['Tahun Pembuatan', $this->mesin->tahun_pembuatan ?? '-']);
        $data->push(['Jenis Mesin', $this->mesin->jenis_mesin ?? '-']);
        $data->push(['Lokasi Instalasi', $this->mesin->lokasi_instalasi ?? '-']);
        $data->push(['Supplier', $this->mesin->supplier ?? '-']);
        $data->push(['Tanggal Pengadaan', $this->mesin->tanggal_pengadaan ? $this->mesin->tanggal_pengadaan->format('d/m/Y') : '-']);
        $data->push(['Harga Pengadaan', $this->mesin->harga_pengadaan ? 'Rp ' . number_format($this->mesin->harga_pengadaan, 0, ',', '.') : '-']);
        $data->push(['No. Invoice', $this->mesin->nomor_invoice ?? '-']);
        $data->push(['Tanggal Garansi Berakhir', $this->mesin->tanggal_waranty_expired ? $this->mesin->tanggal_waranty_expired->format('d/m/Y') : '-']);
        $data->push(['Umur Ekonomis', ($this->mesin->umur_ekonomis_tahun ?? '-') . ' tahun']);
        $data->push(['Status', ucfirst($this->mesin->status ?? '-')]);
        $data->push(['Kondisi Terakhir', $this->mesin->kondisi_terakhir ?? '-']);
        $data->push(['Penanggung Jawab', $this->mesin->pemilik?->name ?? '-']);
        
        $data->push(['', '']);

        // Komponen
        $data->push(['DAFTAR KOMPONEN (' . $this->mesin->komponens->count() . ' Komponen)', '']);
        $data->push(['No', 'Nama Komponen', 'Spesifikasi', 'Status Komponen']);

        foreach ($this->mesin->komponens as $index => $komponen) {
            $data->push([
                $index + 1,
                $komponen->nama_komponen,
                $komponen->spesifikasi ?? '-',
                ucfirst($komponen->status_komponen ?? '-'),
            ]);
        }

        $data->push(['', '', '', '']);

        // Riwayat Perawatan Komponen
        $pergantianKomponen = $this->mesin->komponens()
            ->where(function($q) {
                $q->whereNotNull('tanggal_perawatan_terakhir')
                  ->orWhere('status_komponen', 'perlu_ganti');
            })
            ->get();

        $data->push(['RIWAYAT PERAWATAN KOMPONEN', '', '', '']);
        $data->push(['No', 'Komponen', 'Jadwal Ganti (Bulan)', 'Tanggal Perawatan Terakhir', 'Estimasi Ganti Berikutnya']);

        foreach ($pergantianKomponen as $index => $komponen) {
            $data->push([
                $index + 1,
                $komponen->nama_komponen,
                $komponen->jadwal_ganti_bulan ? $komponen->jadwal_ganti_bulan . ' bulan' : '-',
                $komponen->tanggal_perawatan_terakhir ? $komponen->tanggal_perawatan_terakhir->format('d/m/Y') : '-',
                $komponen->estimasi_tanggal_ganti_berikutnya ? $komponen->estimasi_tanggal_ganti_berikutnya->format('d/m/Y') : '-',
            ]);
        }

        if ($pergantianKomponen->isEmpty()) {
            $data->push(['Tidak ada riwayat perawatan komponen', '', '', '']);
        }

        $data->push(['', '', '', '']);

        // Riwayat Maintenance
        $data->push(['RIWAYAT MAINTENANCE (' . $this->mesin->requests->count() . ' Request)', '', '', '']);
        $data->push(['No', 'Tanggal', 'Deskripsi', 'Status']);

        foreach ($this->mesin->requests->take(10) as $index => $request) {
            $data->push([
                $index + 1,
                $request->created_at->format('d/m/Y H:i'),
                substr($request->deskripsi ?? '-', 0, 50),
                ucfirst(str_replace('_', ' ', $request->status ?? '-')),
            ]);
        }

        if ($this->mesin->requests->isEmpty()) {
            $data->push(['Tidak ada riwayat maintenance', '', '', '']);
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [];

        // Section keywords
        $sectionKeywords = ['DATA MESIN', 'DAFTAR KOMPONEN', 'RIWAYAT PERAWATAN', 'RIWAYAT MAINTENANCE'];
        $sectionHeaderRows = [];
        
        // Find section header rows
        for ($i = 8; $i <= $sheet->getHighestRow(); $i++) {
            $cell = $sheet->getCellByColumnAndRow(1, $i);
            if ($cell) {
                $cellValue = strtoupper($cell->getValue() ?? '');
                foreach ($sectionKeywords as $keyword) {
                    if (strpos($cellValue, $keyword) !== false && strlen($cellValue) > 3) {
                        $sectionHeaderRows[] = $i;
                        break;
                    }
                }
            }
        }

        // Apply section header styling (bold, gray background, centered)
        foreach ($sectionHeaderRows as $row) {
            if ($row <= $sheet->getHighestRow()) {
                $styles[$row] = [
                    'font' => ['bold' => true, 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
            }
        }

        // Apply column header styling (bold, row setelah section header)
        foreach ($sectionHeaderRows as $row) {
            $headerRow = $row + 1;
            if ($headerRow <= $sheet->getHighestRow()) {
                $styles[$headerRow] = [
                    'font' => ['bold' => true, 'size' => 10],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ];
            }
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,  // Label column / No column
            'B' => 35,  // Value column / Data column
            'C' => 25,  // Spesifikasi / Jadwal Ganti
            'D' => 25,  // Status Komponen / Tanggal
            'E' => 25,  // Estimasi Ganti Berikutnya
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastDataRow = $sheet->getHighestRow();

                // ===== HEADER SECTION =====
                // Row height for logo area
                $sheet->getRowDimension(1)->setRowHeight(40);
                $sheet->getRowDimension(2)->setRowHeight(18);

                // Title (shifted right to make room for logo in column A)
                $sheet->setCellValue('B1', 'PT PARAMA BINA ENERGI');
                $sheet->mergeCells('B1:E1');
                $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(14);

                // Departemen
                $sheet->setCellValue('B2', 'Departemen: PRODUKSI');
                $sheet->mergeCells('B2:E2');

                // Info kanan
                $sheet->setCellValue('F1', 'No. Dokumen:');
                $sheet->setCellValue('F2', 'Periode:');
                $sheet->setCellValue('G2', now()->translatedFormat('F Y'));

                // Title Report
                $sheet->setCellValue('A5', 'DETAIL MESIN - ' . $this->mesin->nama_mesin);
                $sheet->mergeCells('A5:E5');
                $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Subtitle tanggal
                $sheet->setCellValue('A6', 'Tanggal: ' . now()->translatedFormat('d F Y H:i'));
                $sheet->mergeCells('A6:E6');
                $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // ===== MERGE SECTION HEADERS =====
                $sectionKeywords = ['DATA MESIN', 'DAFTAR KOMPONEN', 'RIWAYAT PERAWATAN', 'RIWAYAT MAINTENANCE'];
                for ($i = 8; $i <= $lastDataRow; $i++) {
                    $cell = $sheet->getCellByColumnAndRow(1, $i);
                    if ($cell) {
                        $cellValue = strtoupper($cell->getValue() ?? '');
                        foreach ($sectionKeywords as $keyword) {
                            if (strpos($cellValue, $keyword) !== false && strlen($cellValue) > 3) {
                                $sheet->mergeCells('A' . $i . ':E' . $i);
                                // Apply center alignment after merge
                                $sheet->getStyle('A' . $i . ':E' . $i)
                                    ->getAlignment()
                                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                    ->setVertical(Alignment::VERTICAL_CENTER);
                                break;
                            }
                        }
                    }
                }

                // ===== BORDERS =====
                $sheet->getStyle('A8:E' . $lastDataRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // ===== ALIGNMENT =====
                $sheet->getStyle('A8:A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                $sheet->getStyle('B8:E' . $lastDataRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);

                // ===== PRINT SETTINGS =====
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
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

