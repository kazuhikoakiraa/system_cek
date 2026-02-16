<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Master Mesin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 15mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #000;
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
            width: 50px;
            height: 50px;
            object-fit: contain;
            display: block;
        }

        .header-info {
            font-size: 8px;
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
            width: 75px;
            font-weight: bold;
        }

        /* Title */
        .title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0 2px 0;
            text-transform: uppercase;
        }

        .subtitle {
            text-align: center;
            font-size: 8px;
            margin-bottom: 12px;
        }

        /* Section */
        .section {
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 9px;
            font-weight: bold;
            background-color: #e0e0e0;
            color: #000;
            padding: 3px 5px;
            margin-bottom: 4px;
            border: 1px solid #000;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .info-table td {
            padding: 2px 4px;
            border: 1px solid #000;
            font-size: 8px;
        }

        .info-table td:first-child {
            width: 30%;
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .info-table td:last-child {
            background-color: #fff;
        }

        /* Detail Table */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            text-align: left;
            font-size: 8px;
        }

        .detail-table th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }

        .detail-table .col-no {
            width: 5%;
            text-align: center;
        }

        .detail-table .col-nama {
            width: 25%;
        }

        /* Footer */
        .footer {
            margin-top: 12px;
            text-align: right;
            font-size: 7px;
        }

        .empty-message {
            text-align: center;
            padding: 10px;
            font-size: 8px;
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

    @foreach($mesins as $mesin)
    <div class="{{ !$loop->last ? 'page-break' : '' }}">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <table class="header-brand">
                    <tr>
                        @if($logoDataUri)
                            <td style="width: 55px; padding-right: 8px;">
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
                        <td>Dokumen</td>
                        <td>Master Mesin</td>
                    </tr>
                    <tr>
                        <td>Halaman</td>
                        <td>{{ $loop->iteration }} / {{ $mesins->count() }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>{{ $tanggalCetak->translatedFormat('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Title -->
        <div class="title">{{ strtoupper($mesin->nama_mesin) }}</div>
        <div class="subtitle">Kode: {{ $mesin->kode_mesin }}</div>

        <!-- Data Mesin -->
        <div class="section">
            <div class="section-title">Data Mesin</div>
            <table class="info-table">
                <tr>
                    <td>Kode Mesin</td>
                    <td>: {{ $mesin->kode_mesin }}</td>
                </tr>
                <tr>
                    <td>Nama Mesin</td>
                    <td>: {{ $mesin->nama_mesin }}</td>
                </tr>
                <tr>
                    <td>Serial Number</td>
                    <td>: {{ $mesin->serial_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Manufacturer</td>
                    <td>: {{ $mesin->manufacturer ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Model Number</td>
                    <td>: {{ $mesin->model_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Tahun Pembuatan</td>
                    <td>: {{ $mesin->tahun_pembuatan ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Jenis Mesin</td>
                    <td>: {{ $mesin->jenis_mesin ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Lokasi Instalasi</td>
                    <td>: {{ $mesin->lokasi_instalasi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Supplier</td>
                    <td>: {{ $mesin->supplier ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Tanggal Pengadaan</td>
                    <td>: {{ $mesin->tanggal_pengadaan ? $mesin->tanggal_pengadaan->translatedFormat('d F Y') : '-' }}</td>
                </tr>
                <tr>
                    <td>Umur Ekonomis</td>
                    <td>: {{ $mesin->umur_ekonomis_tahun ?? '-' }} tahun</td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td>: {{ ucfirst($mesin->status) }}</td>
                </tr>
                <tr>
                    <td>Kondisi Terakhir</td>
                    <td>: {{ $mesin->kondisi_terakhir ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <!-- Komponen -->
        <div class="section">
            <div class="section-title">Daftar Komponen ({{ $mesin->komponens->count() }} Komponen)</div>
            @if($mesin->komponens->count() > 0)
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-nama">Nama Komponen</th>
                            <th>Spesifikasi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mesin->komponens as $index => $komponen)
                        <tr>
                            <td class="col-no">{{ $index + 1 }}</td>
                            <td class="col-nama">{{ $komponen->nama_komponen }}</td>
                            <td>{{ $komponen->spesifikasi ?? '-' }}</td>
                            <td>{{ ucfirst($komponen->status_komponen ?? '-') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-message">Tidak ada komponen untuk mesin ini</div>
            @endif
        </div>

        <!-- Riwayat Pergantian Komponen -->
        @php
            $pergantianKomponen = $mesin->komponens()
                ->where(function($q) {
                    $q->whereNotNull('tanggal_perawatan_terakhir')
                      ->orWhere('status_komponen', 'perlu_ganti');
                })
                ->get();
        @endphp
        <div class="section">
            <div class="section-title">Riwayat Perawatan Komponen</div>
            @if($pergantianKomponen->count() > 0)
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-nama">Komponen</th>
                            <th>Jadwal Ganti (Bulan)</th>
                            <th>Tanggal Perawatan Terakhir</th>
                            <th>Estimasi Ganti Berikutnya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pergantianKomponen as $index => $komponen)
                        <tr>
                            <td class="col-no">{{ $index + 1 }}</td>
                            <td class="col-nama">{{ $komponen->nama_komponen }}</td>
                            <td style="text-align: center;">{{ $komponen->jadwal_ganti_bulan ? $komponen->jadwal_ganti_bulan . ' bulan' : '-' }}</td>
                            <td>{{ $komponen->tanggal_perawatan_terakhir ? $komponen->tanggal_perawatan_terakhir->translatedFormat('d/m/Y') : '-' }}</td>
                            <td>{{ $komponen->estimasi_tanggal_ganti_berikutnya ? $komponen->estimasi_tanggal_ganti_berikutnya->translatedFormat('d/m/Y') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="empty-message">Tidak ada riwayat perawatan komponen</div>
            @endif
        </div>

        <!-- Riwayat Maintenance -->
        <div class="section">
            <div class="section-title">Riwayat Maintenance ({{ $mesin->requests->count() }} Request)</div>
            @if($mesin->requests->count() > 0)
                <table class="detail-table">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mesin->requests->take(5) as $index => $request)
                        <tr>
                            <td class="col-no">{{ $index + 1 }}</td>
                            <td>{{ $request->created_at->translatedFormat('d/m/Y H:i') }}</td>
                            <td>{{ Str::limit($request->deskripsi ?? '-', 40) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $request->status ?? '-')) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($mesin->requests->count() > 5)
                    <div style="font-size: 8px; color: #666; margin-top: 4px;">
                        ... dan {{ $mesin->requests->count() - 5 }} request lainnya
                    </div>
                @endif
            @else
                <div class="empty-message">Tidak ada riwayat maintenance</div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            Dicetak pada: {{ $tanggalCetak->translatedFormat('d F Y H:i:s') }}
        </div>
    </div>
    @endforeach
</body>
</html>
