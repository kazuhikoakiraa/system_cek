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

        .col-kode {
            width: 8%;
        }

        .col-nama {
            width: 15%;
        }

        .col-jenis {
            width: 10%;
        }

        .col-status {
            width: 8%;
            text-align: center;
        }

        .col-tanggal {
            width: 9%;
            text-align: center;
        }

        .col-penanggung-jawab {
            width: 12%;
        }

        .col-kondisi {
            width: 10%;
        }

        .col-komponen {
            width: 7%;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .status-aktif {
            background-color: #d4edda;
            color: #155724;
        }

        .status-nonaktif {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .status-maintenance {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-rusak {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Footer */
        .footer {
            margin-top: 15px;
            font-size: 7px;
        }

        .footer-table {
            width: 100%;
        }

        .footer-table td {
            padding: 3px;
        }

        .signature-section {
            margin-top: 20px;
            width: 100%;
        }

        .signature-box {
            display: inline-block;
            width: 30%;
            text-align: center;
            vertical-align: top;
        }

        .signature-title {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 50px;
        }

        .signature-name {
            font-size: 8px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 3px;
            display: inline-block;
            min-width: 120px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <table class="header-brand">
                <tr>
                    <td style="width: 60px;">
                        @if(file_exists(public_path('favicon.png')))
                            <img src="{{ public_path('favicon.png') }}" alt="Logo" class="brand-logo">
                        @endif
                    </td>
                    <td>
                        <div class="company-name">PT PARAMA BINA ENERGI</div>
                        <div class="header-info">
                            Jl. Contoh No. 123, Jakarta<br>
                            Telp: (021) 1234567 | Email: info@company.com
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <div class="header-right">
            <table class="doc-info">
                <tr>
                    <td>Dokumen</td>
                    <td>Daftar Master Mesin</td>
                </tr>
                <tr>
                    <td>Tanggal Cetak</td>
                    <td>{{ $tanggalCetak->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>Total Mesin</td>
                    <td>{{ $mesins->count() }} Unit</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Title -->
    <div class="title">DAFTAR MASTER MESIN</div>
    <div class="subtitle">Periode Cetak: {{ $tanggalCetak->format('d F Y') }}</div>

    <!-- Table -->
    <table class="report-table">
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-kode">Kode Mesin</th>
                <th class="col-nama">Nama Mesin</th>
                <th class="col-jenis">Jenis/Model</th>
                <th class="col-status">Status</th>
                <th class="col-tanggal">Tgl Pengadaan</th>
                <th class="col-penanggung-jawab">Penanggung Jawab</th>
                <th class="col-kondisi">Kondisi</th>
                <th class="col-komponen">Jml Komponen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mesins as $index => $mesin)
            <tr>
                <td class="col-no">{{ $index + 1 }}</td>
                <td class="col-kode">{{ $mesin->kode_mesin }}</td>
                <td class="col-nama">
                    <strong>{{ $mesin->nama_mesin }}</strong>
                    @if($mesin->serial_number)
                    <br><small>SN: {{ $mesin->serial_number }}</small>
                    @endif
                </td>
                <td class="col-jenis">
                    {{ $mesin->jenis_mesin ?? '-' }}
                    @if($mesin->model_number)
                    <br><small>{{ $mesin->model_number }}</small>
                    @endif
                </td>
                <td class="col-status">
                    @php
                        $statusClass = match($mesin->status) {
                            'aktif' => 'status-aktif',
                            'nonaktif' => 'status-nonaktif',
                            'maintenance' => 'status-maintenance',
                            'rusak' => 'status-rusak',
                            default => 'status-nonaktif'
                        };
                        $statusText = match($mesin->status) {
                            'aktif' => '✅ Aktif',
                            'nonaktif' => '⏸ Non-Aktif',
                            'maintenance' => '🔧 Maintenance',
                            'rusak' => '❌ Rusak',
                            default => $mesin->status
                        };
                    @endphp
                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                </td>
                <td class="col-tanggal">
                    {{ $mesin->tanggal_pengadaan ? $mesin->tanggal_pengadaan->format('d/m/Y') : '-' }}
                </td>
                <td class="col-penanggung-jawab">{{ $mesin->pemilik?->name ?? '-' }}</td>
                <td class="col-kondisi">{{ $mesin->kondisi_terakhir ?? '-' }}</td>
                <td class="col-komponen">{{ $mesin->komponens->count() }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td style="width: 70%;">
                    <strong>Catatan:</strong><br>
                    Dokumen ini dicetak secara otomatis dari sistem manajemen mesin.<br>
                    Untuk informasi lebih detail, silakan akses sistem atau hubungi administrator.
                </td>
                <td style="width: 30%; text-align: right;">
                    <strong>Statistik:</strong><br>
                    Total Aktif: {{ $mesins->where('status', 'aktif')->count() }}<br>
                    Total Maintenance: {{ $mesins->where('status', 'maintenance')->count() }}<br>
                    Total Rusak: {{ $mesins->where('status', 'rusak')->count() }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Signature -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-title">Dibuat Oleh,</div>
            <div class="signature-name">_________________</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Diperiksa Oleh,</div>
            <div class="signature-name">_________________</div>
        </div>
        <div class="signature-box">
            <div class="signature-title">Disetujui Oleh,</div>
            <div class="signature-name">_________________</div>
        </div>
    </div>
</body>
</html>
