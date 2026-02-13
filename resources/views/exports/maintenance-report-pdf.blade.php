<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Maintenance Mesin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4 landscape;
            margin: 15mm 12mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            line-height: 1.2;
        }

        .page-break {
            page-break-after: always;
        }

        .page-break:last-child {
            page-break-after: avoid;
        }

        /* Header Section */
        .header {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            border: 1px solid #000;
        }

        .header-left {
            display: table-cell;
            width: 60%;
            padding: 6px;
            vertical-align: middle;
            border-right: 1px solid #000;
        }

        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }

        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .header-brand {
            width: 100%;
            border-collapse: collapse;
        }

        .header-brand td {
            vertical-align: middle;
        }

        .brand-logo {
            width: 55px;
            height: 55px;
            object-fit: contain;
            display: block;
        }

        .header-info {
            font-size: 9px;
        }

        .header-info table {
            width: 100%;
        }

        .header-info td {
            padding: 1px 4px;
            font-size: 8px;
        }

        .header-info td:first-child {
            width: 80px;
        }

        .doc-info {
            width: 100%;
        }

        .doc-info td {
            padding: 2px 6px;
            border-bottom: 1px solid #000;
            font-size: 8px;
        }

        .doc-info tr:last-child td {
            border-bottom: none;
        }

        .doc-info td:first-child {
            width: 80px;
            font-weight: 600;
        }

        /* Title */
        .title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin: 8px 0 4px 0;
            text-transform: uppercase;
        }

        .subtitle {
            text-align: center;
            font-size: 9px;
            margin-bottom: 8px;
        }

        /* Table */
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .report-table th,
        .report-table td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
            vertical-align: top;
            font-size: 7px;
        }

        .report-table th {
            background-color: #d9d9d9;
            font-weight: bold;
            text-align: center;
            font-size: 7px;
        }

        .col-no {
            width: 3%;
            text-align: center;
        }

        .col-date {
            width: 9%;
        }

        .col-mesin {
            width: 11%;
        }

        .col-komponen {
            width: 11%;
        }

        .col-issue {
            width: 18%;
        }

        .col-status {
            width: 8%;
            text-align: center;
        }

        .col-teknisi {
            width: 11%;
        }

        .col-dates {
            width: 9%;
        }

        .col-sparepart {
            width: 12%;
        }

        .col-catatan {
            width: 12%;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-in-progress {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }

        /* Summary */
        .summary {
            margin-top: 8px;
            padding: 5px;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            font-size: 7px;
        }

        .summary-title {
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 8px;
        }

        .summary-item {
            margin: 2px 0;
        }

        /* Footer notes */
        .footer-notes {
            margin-top: 10px;
            font-size: 7px;
        }

        .footer-notes-title {
            font-weight: bold;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('favicon.png');
        $logoDataUri = null;
        if (file_exists($logoPath)) {
            $imageData = base64_encode(file_get_contents($logoPath));
            $mimeType = mime_content_type($logoPath);
            $logoDataUri = "data:{$mimeType};base64,{$imageData}";
        }
    @endphp

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <table class="header-brand">
                <tr>
                    @if($logoDataUri)
                        <td style="width: 60px; padding-right: 8px;">
                            <img class="brand-logo" src="{{ $logoDataUri }}" alt="Logo">
                        </td>
                    @endif
                    <td>
                        <div class="company-name">PT PARAMA BINA ENERGI</div>
                        <div class="header-info">
                            <table>
                                <tr>
                                    <td>Departemen</td>
                                    <td>: PRODUKSI</td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="header-right">
            <table class="doc-info">
                <tr>
                    <td>No. Dokumen</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Periode</td>
                    <td>
                        @if($tanggalMulai && $tanggalSelesai)
                            {{ $tanggalMulai->format('d/m/Y') }} - {{ $tanggalSelesai->format('d/m/Y') }}
                        @elseif($tanggalMulai)
                            Sejak {{ $tanggalMulai->format('d/m/Y') }}
                        @elseif($tanggalSelesai)
                            Sampai {{ $tanggalSelesai->format('d/m/Y') }}
                        @else
                            Semua Data
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Halaman</td>
                    <td>1 / 1</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Title -->
    <div class="title">LAPORAN MAINTENANCE</div>
    <div class="subtitle">
        Periode: 
        @if($tanggalMulai && $tanggalSelesai)
            {{ $tanggalMulai->translatedFormat('d F Y') }} - {{ $tanggalSelesai->translatedFormat('d F Y') }}
        @elseif($tanggalMulai)
            Sejak {{ $tanggalMulai->translatedFormat('d F Y') }}
        @elseif($tanggalSelesai)
            Sampai {{ $tanggalSelesai->translatedFormat('d F Y') }}
        @else
            Semua Data
        @endif
    </div>

    <!-- Summary Table -->
    <table class="report-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-date">Tanggal</th>
                <th class="col-mesin">Mesin</th>
                <th class="col-komponen">Komponen</th>
                <th class="col-issue">Deskripsi Masalah</th>
                <th class="col-status">Status</th>
                <th class="col-teknisi">Teknisi</th>
                <th class="col-dates">Tgl Mulai</th>
                <th class="col-dates">Tgl Selesai</th>
                <th class="col-sparepart">Spare Parts</th>
                @if($includeDetail)
                    <th class="col-catatan">Catatan</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($laporanData as $index => $report)
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-date">{{ $report->created_at->format('d/m/y H:i') }}</td>
                    <td class="col-mesin">{{ $report->mesin?->nama_mesin ?? '-' }}</td>
                    <td class="col-komponen">{{ $report->komponenMesin?->nama_komponen ?? '-' }}</td>
                    <td class="col-issue">{{ $report->issue_description ?? '-' }}</td>
                    <td class="col-status">
                        @php
                            $statusClass = match($report->status) {
                                'pending' => 'status-pending',
                                'in_progress' => 'status-in-progress',
                                'completed' => 'status-completed',
                                default => ''
                            };
                            $statusLabel = match($report->status) {
                                'pending' => 'Tunggu',
                                'in_progress' => 'Proses',
                                'completed' => 'Selesai',
                                default => '-'
                            };
                        @endphp
                        <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="col-teknisi">{{ $report->teknisi?->name ?? '-' }}</td>
                    <td class="col-dates">{{ $report->tanggal_mulai ? $report->tanggal_mulai->format('d/m/y') : '-' }}</td>
                    <td class="col-dates">{{ $report->tanggal_selesai ? $report->tanggal_selesai->format('d/m/y') : '-' }}</td>
                    <td class="col-sparepart">
                        @if($report->spareParts->count() > 0)
                            @foreach($report->spareParts as $sp)
                                {{ $sp->nama_suku_cadang }} ({{ $sp->pivot->jumlah_digunakan }}){{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        @else
                            -
                        @endif
                    </td>
                    @if($includeDetail)
                        <td class="col-catatan">{{ $report->catatan_teknisi ?? '-' }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $includeDetail ? '11' : '10' }}" style="text-align: center; padding: 15px;">Tidak ada data maintenance</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-title">Ringkasan Laporan:</div>
        <div class="summary-item">Total Laporan: {{ $laporanData->count() }}</div>
        @php
            $statusSummary = $laporanData->groupBy('status')->map(fn($items) => $items->count());
        @endphp
        @if($statusSummary->has('pending'))
            <div class="summary-item">- Menunggu: {{ $statusSummary['pending'] }}</div>
        @endif
        @if($statusSummary->has('in_progress'))
            <div class="summary-item">- Sedang Diproses: {{ $statusSummary['in_progress'] }}</div>
        @endif
        @if($statusSummary->has('completed'))
            <div class="summary-item">- Selesai: {{ $statusSummary['completed'] }}</div>
        @endif
    </div>

    <!-- Footer Notes -->
    <div class="footer-notes">
        <div class="footer-notes-title">Keterangan:</div>
        <div>• Data ini dihasilkan secara otomatis dari sistem</div>
        <div>• Untuk informasi lebih detail, silakan cek pada sistem</div>
    </div>
</body>
</html>
