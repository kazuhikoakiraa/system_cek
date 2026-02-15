<?php

namespace App\Exports;

use App\Models\Mesin;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class MesinLengkapExport implements WithMultipleSheets
{
    protected $mesinId;

    public function __construct($mesinId = null)
    {
        $this->mesinId = $mesinId;
    }

    public function sheets(): array
    {
        $sheets = [];

        if ($this->mesinId) {
            // Export untuk satu mesin spesifik
            $mesin = Mesin::findOrFail($this->mesinId);
            $sheets[] = new MesinDetailSheet($mesin);
            $sheets[] = new MesinKomponenSheet($mesin);
            $sheets[] = new MesinMaintenanceHistorySheet($mesin);
            $sheets[] = new MesinAuditTrailSheet($mesin);
        } else {
            // Export untuk semua mesin
            $sheets[] = new AllMesinSheet();
        }

        return $sheets;
    }
}

class AllMesinSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function collection()
    {
        return Mesin::with(['pemilik', 'komponens', 'requests'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Mesin',
            'Serial Number',
            'Nama Mesin',
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
            'Estimasi Penggantian',
            'Status',
            'Kondisi Terakhir',
            'Penanggung Jawab',
            'Jumlah Komponen',
            'Jumlah Request',
            'Spesifikasi Teknis',
            'Catatan',
            'Dibuat Pada',
        ];
    }

    public function map($mesin): array
    {
        return [
            $mesin->id,
            $mesin->kode_mesin,
            $mesin->serial_number,
            $mesin->nama_mesin,
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
            $mesin->estimasi_penggantian ? $mesin->estimasi_penggantian->format('d/m/Y') : '',
            $mesin->status,
            $mesin->kondisi_terakhir,
            $mesin->pemilik?->name,
            $mesin->komponens->count(),
            $mesin->requests->count(),
            $mesin->spesifikasi_teknis,
            $mesin->catatan,
            $mesin->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
        ];
    }

    public function title(): string
    {
        return 'Daftar Semua Mesin';
    }
}

class MesinDetailSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $mesin;

    public function __construct(Mesin $mesin)
    {
        $this->mesin = $mesin;
    }

    public function collection()
    {
        return collect([$this->mesin]);
    }

    public function headings(): array
    {
        return [
            'Atribut',
            'Nilai',
        ];
    }

    public function map($mesin): array
    {
        return [
            ['Kode Mesin', $mesin->kode_mesin],
            ['Serial Number', $mesin->serial_number],
            ['Nama Mesin', $mesin->nama_mesin],
            ['Manufaktur', $mesin->manufacturer],
            ['Model', $mesin->model_number],
            ['Tahun Pembuatan', $mesin->tahun_pembuatan],
            ['Jenis Mesin', $mesin->jenis_mesin],
            ['Lokasi Instalasi', $mesin->lokasi_instalasi],
            ['Supplier', $mesin->supplier],
            ['Tanggal Pengadaan', $mesin->tanggal_pengadaan ? $mesin->tanggal_pengadaan->format('d/m/Y') : ''],
            ['Harga Pengadaan', $mesin->harga_pengadaan ? 'Rp ' . number_format($mesin->harga_pengadaan, 2, ',', '.') : ''],
            ['No. Invoice', $mesin->nomor_invoice],
            ['Tanggal Garansi Berakhir', $mesin->tanggal_waranty_expired ? $mesin->tanggal_waranty_expired->format('d/m/Y') : ''],
            ['Umur Ekonomis', $mesin->umur_ekonomis_tahun ? $mesin->umur_ekonomis_tahun . ' tahun' : ''],
            ['Estimasi Penggantian', $mesin->estimasi_penggantian ? $mesin->estimasi_penggantian->format('d/m/Y') : ''],
            ['Status', $mesin->status],
            ['Kondisi Terakhir', $mesin->kondisi_terakhir],
            ['Penanggung Jawab', $mesin->pemilik?->name],
            ['Spesifikasi Teknis', $mesin->spesifikasi_teknis],
            ['Catatan', $mesin->catatan],
            ['Dibuat Pada', $mesin->created_at->format('d/m/Y H:i')],
            ['Diupdate Pada', $mesin->updated_at->format('d/m/Y H:i')],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            ],
            'A:A' => [
                'font' => ['bold' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7E6E6']],
            ],
        ];
    }

    public function title(): string
    {
        return 'Detail Mesin';
    }
}

class MesinKomponenSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $mesin;

    public function __construct(Mesin $mesin)
    {
        $this->mesin = $mesin;
    }

    public function collection()
    {
        return $this->mesin->komponens;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Komponen',
            'Manufaktur',
            'Part Number',
            'Lokasi Pemasangan',
            'Tanggal Pengadaan',
            'Jadwal Ganti (Bulan)',
            'Perawatan Terakhir',
            'Estimasi Ganti Berikutnya',
            'Status',
            'Supplier',
            'Harga',
            'Jumlah Terpasang',
            'Stok Minimal',
            'Spesifikasi Teknis',
            'Catatan',
        ];
    }

    public function map($komponen): array
    {
        return [
            $komponen->id,
            $komponen->nama_komponen,
            $komponen->manufacturer,
            $komponen->part_number,
            $komponen->lokasi_pemasangan,
            $komponen->tanggal_pengadaan ? $komponen->tanggal_pengadaan->format('d/m/Y') : '',
            $komponen->jadwal_ganti_bulan,
            $komponen->tanggal_perawatan_terakhir ? $komponen->tanggal_perawatan_terakhir->format('d/m/Y') : '',
            $komponen->estimasi_tanggal_ganti_berikutnya ? $komponen->estimasi_tanggal_ganti_berikutnya->format('d/m/Y') : '',
            $komponen->status_komponen,
            $komponen->nama_supplier,
            $komponen->harga_komponen ? 'Rp ' . number_format($komponen->harga_komponen, 2, ',', '.') : '',
            $komponen->jumlah_terpasang,
            $komponen->stok_minimal,
            $komponen->spesifikasi_teknis,
            $komponen->catatan,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '70AD47']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
        ];
    }

    public function title(): string
    {
        return 'Komponen';
    }
}

class MesinMaintenanceHistorySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $mesin;

    public function __construct(Mesin $mesin)
    {
        $this->mesin = $mesin;
    }

    public function collection()
    {
        return $this->mesin->requests()
            ->with(['komponen', 'creator', 'approver', 'logs.teknisi'])
            ->orderBy('requested_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No. Request',
            'Komponen',
            'Deskripsi Masalah',
            'Tingkat Urgensi',
            'Status',
            'Dibuat Oleh',
            'Tanggal Request',
            'Disetujui Oleh',
            'Tanggal Approval',
            'Catatan Approval',
            'Jumlah Log Perbaikan',
            'Tanggal Ditolak',
            'Alasan Ditolak',
        ];
    }

    public function map($request): array
    {
        return [
            $request->request_number,
            $request->komponen?->nama_komponen,
            $request->problema_deskripsi,
            $request->urgency_level,
            $request->status,
            $request->creator?->name,
            $request->requested_at ? $request->requested_at->format('d/m/Y H:i') : '',
            $request->approver?->name,
            $request->approved_at ? $request->approved_at->format('d/m/Y H:i') : '',
            $request->approval_notes,
            $request->logs->count(),
            $request->rejected_at ? $request->rejected_at->format('d/m/Y H:i') : '',
            $request->rejection_reason,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC000']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
        ];
    }

    public function title(): string
    {
        return 'Riwayat Maintenance';
    }
}

class MesinAuditTrailSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $mesin;

    public function __construct(Mesin $mesin)
    {
        $this->mesin = $mesin;
    }

    public function collection()
    {
        return $this->mesin->audits()
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Tanggal & Waktu',
            'Jenis Aksi',
            'User',
            'Deskripsi',
            'IP Address',
            'User Agent',
        ];
    }

    public function map($audit): array
    {
        return [
            $audit->id,
            $audit->created_at->format('d/m/Y H:i:s'),
            $audit->action_type,
            $audit->user?->name,
            $audit->deskripsi_perubahan,
            $audit->ip_address,
            $audit->user_agent,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'ED7D31']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ],
        ];
    }

    public function title(): string
    {
        return 'Audit Trail';
    }
}
