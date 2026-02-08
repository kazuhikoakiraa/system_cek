<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pengecekan Mesin</title>
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
            border-right: 1px solid #000;
            width: 85px;
            font-weight: bold;
        }

        /* Title */
        .title {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            margin: 8px 0 4px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 9px;
            margin-bottom: 8px;
        }

        /* Main Table */
        .check-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .check-table th,
        .check-table td {
            border: 1px solid #000;
            padding: 2px 1px;
            text-align: center;
            font-size: 7px;
        }

        .check-table th {
            background-color: #d9d9d9;
            font-weight: bold;
        }

        .check-table .col-no {
            width: 20px;
        }

        .check-table .col-item {
            width: 130px;
            text-align: left;
            padding-left: 4px;
        }

        .check-table .col-standar {
            width: 90px;
            text-align: left;
            padding-left: 4px;
        }

        .check-table .col-frekuensi {
            width: 50px;
        }

        .check-table .col-day {
            width: 16px;
            min-width: 16px;
        }

        .check-table .col-ket {
            width: 85px;
            text-align: left;
            padding-left: 4px;
        }

        .check-table .day-header {
            background-color: #e6e6e6;
        }

        .check-ok {
            color: #006600;
            font-weight: bold;
        }

        .check-ng {
            color: #cc0000;
            font-weight: bold;
        }

        .check-skip {
            color: #ff9900;
            font-weight: bold;
        }

        /* Sunday/Weekend column styling */
        .col-sunday {
            background-color: #ffe6e6;
        }

        /* Keterangan Section */
        .keterangan-section {
            margin-top: 10px;
            margin-bottom: 15px;
        }

        .keterangan-section h4 {
            font-size: 9px;
            margin-bottom: 4px;
        }

        .keterangan-section ul {
            list-style: none;
            font-size: 8px;
        }

        .keterangan-section li {
            margin-bottom: 1px;
        }

        /* Signature Section */
        .signature-section {
            width: 100%;
            margin-top: 20px;
        }

        .signature-table {
            width: 100%;
        }

        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 3px 15px;
        }

        .signature-label {
            font-size: 9px;
            margin-bottom: 40px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin: 0 15px;
            padding-top: 35px;
        }

        .signature-name {
            font-size: 8px;
            margin-top: 4px;
        }

        .signature-title {
            font-size: 8px;
            font-weight: bold;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            padding: 5px;
        }
    </style>
</head>
<body>
    @php
        $logoCandidates = [
            public_path('images/logo.png'),
            public_path('images/logo.jpg'),
            public_path('images/logo.jpeg'),
            public_path('favicon.png'),
        ];
        $logoFile = null;
        foreach ($logoCandidates as $candidate) {
            if (is_string($candidate) && file_exists($candidate)) {
                $logoFile = $candidate;
                break;
            }
        }

        $logoDataUri = null;
        if ($logoFile) {
            $ext = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                default => null,
            };
            if ($mime) {
                $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoFile));
            }
        }
    @endphp
    @foreach($laporanData as $mesin)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
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
                        <td>{{ $nomorDokumen }}</td>
                    </tr>
                    <tr>
                        <td>Revisi</td>
                        <td>00</td>
                    </tr>
                    <tr>
                        <td>Periode</td>
                        <td>{{ $tanggalMulai->translatedFormat('F Y') }}</td>
                    </tr>
                    <tr>
                        <td>Halaman</td>
                        <td>{{ $loop->iteration }} / {{ $laporanData->count() }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Title -->
        <div class="title">CHECK SHEET {{ strtoupper($mesin->nama_mesin) }}</div>
        <div class="subtitle">
            Periode: {{ $tanggalMulai->translatedFormat('d F Y') }} - {{ $tanggalSelesai->translatedFormat('d F Y') }}
        </div>

        <!-- Check Table -->
        @php
            $tanggalRange = [];
            $current = $tanggalMulai->copy();
            while ($current <= $tanggalSelesai) {
                $tanggalRange[] = $current->copy();
                $current->addDay();
            }
        @endphp

        <table class="check-table">
            <thead>
                <tr>
                    <th class="col-no" rowspan="2">No</th>
                    <th class="col-item" rowspan="2">Item/Komponen</th>
                    <th class="col-standar" rowspan="2">Standar</th>
                    <th class="col-frekuensi" rowspan="2">Frekuensi</th>
                    <th colspan="{{ count($tanggalRange) }}" class="day-header">Tanggal</th>
                    <th class="col-ket" rowspan="2">Keterangan</th>
                </tr>
                <tr>
                    @foreach($tanggalRange as $tanggal)
                        <th class="col-day {{ $tanggal->dayOfWeek === 0 ? 'col-sunday' : '' }}">{{ $tanggal->format('d') }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($mesin->komponenMesins as $index => $komponen)
                    <tr>
                        <td class="col-no">{{ $index + 1 }}</td>
                        <td class="col-item">{{ $komponen->nama_komponen }}</td>
                        <td class="col-standar">{{ $komponen->standar ?? '-' }}</td>
                        <td class="col-frekuensi">{{ ucfirst($komponen->frekuensi ?? 'harian') }}</td>

                        @foreach($tanggalRange as $tanggal)
                            @php
                                // Skip hari Minggu - kosongkan saja
                                if ($tanggal->dayOfWeek === 0) {
                                    echo '<td class="col-day col-sunday"></td>';
                                    continue;
                                }

                                $pengecekan = $mesin->pengecekan
                                    ->first(fn($p) => $p->tanggal_pengecekan->isSameDay($tanggal));

                                $status = '-';
                                $class = '';

                                if ($pengecekan) {
                                    $detail = $pengecekan->detailPengecekan
                                        ->first(fn($d) => $d->komponen_mesin_id === $komponen->id);

                                    if ($detail) {
                                        if ($detail->status_sesuai === 'sesuai') {
                                            $status = '✓';
                                            $class = 'check-ok';
                                        } elseif ($detail->status_sesuai === 'tidak_sesuai') {
                                            $status = '✗';
                                            $class = 'check-ng';
                                        }
                                    }
                                }
                            @endphp
                            <td class="col-day {{ $class }}">{{ $status }}</td>
                        @endforeach

                        @php
                            $lastPengecekan = $mesin->pengecekan->last();
                            $keterangan = '-';
                            if ($lastPengecekan) {
                                $lastDetail = $lastPengecekan->detailPengecekan
                                    ->first(fn($d) => $d->komponen_mesin_id === $komponen->id);
                                if ($lastDetail && $lastDetail->keterangan) {
                                    $keterangan = $lastDetail->keterangan;
                                }
                            }
                        @endphp
                        <td class="col-ket">{{ $keterangan }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 5 + count($tanggalRange) }}" style="text-align: center; padding: 20px;">
                            Tidak ada komponen untuk mesin ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Keterangan -->
        <div class="keterangan-section">
            <h4>Keterangan:</h4>
            <ul>
                <li>✓ = Sesuai/OK - Kondisi komponen dalam keadaan baik</li>
                <li>✗ = Tidak Sesuai/NG - Kondisi komponen memerlukan perbaikan</li>
                <li>- = Tidak ada data pengecekan/tidak dicek pada tanggal tersebut</li>
            </ul>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div style="text-align: left; font-size: 9px; margin-bottom: 15px;">
                PEMATANGSIANTAR, {{ now()->translatedFormat('d F Y') }}
            </div>
            <table class="signature-table">
                <tr>
                    <td>
                        <div class="signature-label">Dibuat oleh,</div>
                        <div class="signature-line"></div>
                        <div class="signature-name">(________________________)</div>
                        <div class="signature-title">Operator</div>
                    </td>
                    <td>
                        <div class="signature-label">Diketahui oleh,</div>
                        <div class="signature-line"></div>
                        <div class="signature-name">(________________________)</div>
                        <div class="signature-title">Kepala Produksi</div>
                    </td>
                    <td>
                        <div class="signature-label">Disetujui oleh,</div>
                        <div class="signature-line"></div>
                        <div class="signature-name">(________________________)</div>
                        <div class="signature-title">Manager</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer Info -->
        <div style="text-align: right; font-size: 8px; margin-top: 10px; color: #666;">
            Dicetak pada: {{ $tanggalCetak->translatedFormat('d F Y H:i:s') }}
        </div>
    </div>
    @endforeach
</body>
</html>
