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
            margin: 10mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
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
            margin-bottom: 10px;
            border: 1px solid #000;
        }

        .header-left {
            display: table-cell;
            width: 60%;
            padding: 8px;
            vertical-align: middle;
            border-right: 1px solid #000;
        }

        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header-info {
            font-size: 10px;
        }

        .header-info table {
            width: 100%;
        }

        .header-info td {
            padding: 2px 5px;
            font-size: 9px;
        }

        .header-info td:first-child {
            width: 80px;
        }

        .doc-info {
            width: 100%;
        }

        .doc-info td {
            padding: 3px 8px;
            border-bottom: 1px solid #000;
            font-size: 9px;
        }

        .doc-info tr:last-child td {
            border-bottom: none;
        }

        .doc-info td:first-child {
            border-right: 1px solid #000;
            width: 90px;
            font-weight: bold;
        }

        /* Title */
        .title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }

        .subtitle {
            text-align: center;
            font-size: 10px;
            margin-bottom: 10px;
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
            padding: 3px 2px;
            text-align: center;
            font-size: 8px;
        }

        .check-table th {
            background-color: #d9d9d9;
            font-weight: bold;
        }

        .check-table .col-no {
            width: 25px;
        }

        .check-table .col-item {
            width: 150px;
            text-align: left;
            padding-left: 5px;
        }

        .check-table .col-standar {
            width: 100px;
            text-align: left;
            padding-left: 5px;
        }

        .check-table .col-frekuensi {
            width: 60px;
        }

        .check-table .col-day {
            width: 18px;
            min-width: 18px;
        }

        .check-table .col-ket {
            width: 100px;
            text-align: left;
            padding-left: 5px;
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

        /* Keterangan Section */
        .keterangan-section {
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .keterangan-section h4 {
            font-size: 10px;
            margin-bottom: 5px;
        }

        .keterangan-section ul {
            list-style: none;
            font-size: 9px;
        }

        .keterangan-section li {
            margin-bottom: 2px;
        }

        /* Signature Section */
        .signature-section {
            width: 100%;
            margin-top: 30px;
        }

        .signature-table {
            width: 100%;
        }

        .signature-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 5px 20px;
        }

        .signature-label {
            font-size: 10px;
            margin-bottom: 50px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            margin: 0 20px;
            padding-top: 40px;
        }

        .signature-name {
            font-size: 9px;
            margin-top: 5px;
        }

        .signature-title {
            font-size: 9px;
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
    @foreach($laporanData as $mesin)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">PT PARMA BINA ENERGI</div>
                <div class="header-info">
                    <table>
                        <tr>
                            <td>Departemen</td>
                            <td>: PRODUKSI</td>
                        </tr>
                        <tr>
                            <td>Bagian</td>
                            <td>: {{ strtoupper($mesin->nama_mesin) }}</td>
                        </tr>
                    </table>
                </div>
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
        <div class="title">CHECK SHEET PENGECEKAN MESIN</div>
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
                        <th class="col-day">{{ $tanggal->format('d') }}</th>
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
