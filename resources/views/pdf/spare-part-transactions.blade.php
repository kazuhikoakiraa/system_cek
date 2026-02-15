<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi Suku Cadang</title>
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

        .col-nomor {
            width: 10%;
        }

        .col-date {
            width: 10%;
        }

        .col-kode {
            width: 9%;
        }

        .col-nama {
            width: 15%;
        }

        .col-tipe {
            width: 7%;
            text-align: center;
        }

        .col-jumlah {
            width: 8%;
            text-align: center;
        }

        .col-stok {
            width: 8%;
            text-align: center;
        }

        .col-keterangan {
            width: 18%;
        }

        .col-user {
            width: 10%;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .tipe-in {
            background-color: #d1fae5;
            color: #065f46;
        }

        .tipe-out {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .tipe-return {
            background-color: #fef3c7;
            color: #92400e;
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

        /* Signature Section */
        .signature-section {
            margin-top: 20px;
            width: 100%;
        }

        .signature-location {
            text-align: left;
            font-size: 8px;
            margin-bottom: 10px;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
        }

        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }

        .signature-title {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 40px;
        }

        .signature-name {
            font-size: 8px;
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            margin-bottom: 3px;
        }

        .signature-position {
            font-size: 7px;
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
    <div class="title">LAPORAN TRANSAKSI SUKU CADANG</div>
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

    <!-- Data Table -->
    <table class="report-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-nomor">No. Transaksi</th>
                <th class="col-date">Tanggal</th>
                <th class="col-kode">Kode</th>
                <th class="col-nama">Nama Suku Cadang</th>
                <th class="col-tipe">Tipe</th>
                <th class="col-jumlah">Jumlah</th>
                <th class="col-stok">Stok Sebelum</th>
                <th class="col-stok">Stok Sesudah</th>
                <th class="col-keterangan">Keterangan</th>
                <th class="col-user">Diinput Oleh</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $transaction)
                <tr>
                    <td class="col-no">{{ $index + 1 }}</td>
                    <td class="col-nomor">{{ $transaction->nomor_transaksi }}</td>
                    <td class="col-date">{{ $transaction->tanggal_transaksi->format('d/m/y H:i') }}</td>
                    <td class="col-kode">{{ $transaction->sparePart->kode_suku_cadang }}</td>
                    <td class="col-nama">{{ $transaction->sparePart->nama_suku_cadang }}</td>
                    <td class="col-tipe">
                        @php
                            $tipeClass = match($transaction->tipe_transaksi) {
                                'IN' => 'tipe-in',
                                'OUT' => 'tipe-out',
                                'RETURN' => 'tipe-return',
                                default => ''
                            };
                            $tipeLabel = match($transaction->tipe_transaksi) {
                                'IN' => 'Masuk',
                                'OUT' => 'Keluar',
                                'RETURN' => 'Retur',
                                default => '-'
                            };
                        @endphp
                        <span class="status-badge {{ $tipeClass }}">{{ $tipeLabel }}</span>
                    </td>
                    <td class="col-jumlah">{{ number_format($transaction->jumlah) }} {{ $transaction->sparePart->satuan }}</td>
                    <td class="col-stok">{{ number_format($transaction->stok_sebelum) }}</td>
                    <td class="col-stok">{{ number_format($transaction->stok_sesudah) }}</td>
                    <td class="col-keterangan">{{ $transaction->keterangan ?? '-' }}</td>
                    <td class="col-user">{{ $transaction->user->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" style="text-align: center; padding: 15px;">Tidak ada data transaksi</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary -->
    <div class="summary">
        <div class="summary-title">Ringkasan Laporan:</div>
        <div class="summary-item">Total Transaksi: {{ $transactions->count() }}</div>
        @php
            $totalMasuk = $transactions->where('tipe_transaksi', 'IN')->sum('jumlah');
            $totalKeluar = $transactions->where('tipe_transaksi', 'OUT')->sum('jumlah');
            $totalRetur = $transactions->where('tipe_transaksi', 'RETURN')->sum('jumlah');
        @endphp
        <div class="summary-item">- Total Masuk: {{ number_format($totalMasuk) }} unit</div>
        <div class="summary-item">- Total Keluar: {{ number_format($totalKeluar) }} unit</div>
        <div class="summary-item">- Total Retur: {{ number_format($totalRetur) }} unit</div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-location">PEMATANGSIANTAR, {{ now()->translatedFormat('d F Y') }}</div>
        <table class="signature-table">
            <tr>
                <td>
                    <div class="signature-title">Dibuat oleh,</div>
                    <div class="signature-name">&nbsp;</div>
                    <div class="signature-position">Staff Gudang</div>
                </td>
                <td>
                    <div class="signature-title">Diketahui oleh,</div>
                    <div class="signature-name">&nbsp;</div>
                    <div class="signature-position">Kepala Gudang</div>
                </td>
                <td>
                    <div class="signature-title">Disetujui oleh,</div>
                    <div class="signature-name">&nbsp;</div>
                    <div class="signature-position">Manager</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer Notes -->
    <div class="footer-notes">
        <div class="footer-notes-title">Keterangan:</div>
        <div>• Data ini dihasilkan secara otomatis dari sistem</div>
        <div>• Untuk informasi lebih detail, silakan cek pada sistem</div>
    </div>
</body>
</html>
