<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Mesin - {{ $mesin->kode_mesin }}</title>
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
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .document-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .mesin-code {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        .section {
            margin-bottom: 15px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            background-color: #4472C4;
            color: white;
            padding: 5px 8px;
            margin-bottom: 8px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 4px 6px;
            border: 1px solid #ddd;
            font-size: 9px;
        }

        .info-table td:first-child {
            width: 35%;
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 8px;
        }

        .detail-table th {
            background-color: #d9d9d9;
            font-weight: bold;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
        }

        .status-aktif { background-color: #d4edda; color: #155724; }
        .status-nonaktif { background-color: #e2e3e5; color: #383d41; }
        .status-maintenance { background-color: #fff3cd; color: #856404; }
        .status-rusak { background-color: #f8d7da; color: #721c24; }

        .status-normal { background-color: #d4edda; color: #155724; }
        .status-perlu-ganti { background-color: #fff3cd; color: #856404; }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            text-align: center;
            color: #666;
        }

        .page-break {
            page-break-after: always;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">PT PARAMA BINA ENERGI</div>
        <div style="font-size: 10px;">Sistem Manajemen Mesin</div>
        <div class="document-title">Detail Informasi Mesin</div>
        <div class="mesin-code">{{ $mesin->kode_mesin }} - {{ $mesin->nama_mesin }}</div>
    </div>

    <!-- Identitas Mesin -->
    <div class="section">
        <div class="section-title">📋 IDENTITAS MESIN</div>
        <table class="info-table">
            <tr>
                <td>Kode Mesin</td>
                <td>{{ $mesin->kode_mesin }}</td>
            </tr>
            <tr>
                <td>Nama Mesin</td>
                <td>{{ $mesin->nama_mesin }}</td>
            </tr>
            <tr>
                <td>Serial Number</td>
                <td>{{ $mesin->serial_number ?? '-' }}</td>
            </tr>
            <tr>
                <td>Manufaktur/Pabrikan</td>
                <td>{{ $mesin->manufacturer ?? '-' }}</td>
            </tr>
            <tr>
                <td>Model/Tipe</td>
                <td>{{ $mesin->model_number ?? '-' }}</td>
            </tr>
            <tr>
                <td>Jenis Mesin</td>
                <td>{{ $mesin->jenis_mesin ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tahun Pembuatan</td>
                <td>{{ $mesin->tahun_pembuatan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
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
            </tr>
            <tr>
                <td>Kondisi Terakhir</td>
                <td>{{ $mesin->kondisi_terakhir ?? '-' }}</td>
            </tr>
            <tr>
                <td>Penanggung Jawab</td>
                <td>{{ $mesin->pemilik?->name ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- Informasi Pengadaan -->
    <div class="section">
        <div class="section-title">💰 INFORMASI PENGADAAN & KEUANGAN</div>
        <table class="info-table">
            <tr>
                <td>Tanggal Pengadaan</td>
                <td>{{ $mesin->tanggal_pengadaan ? $mesin->tanggal_pengadaan->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Harga Pengadaan</td>
                <td>{{ $mesin->harga_pengadaan ? 'Rp ' . number_format($mesin->harga_pengadaan, 0, ',', '.') : '-' }}</td>
            </tr>
            <tr>
                <td>Nomor Invoice/PO</td>
                <td>{{ $mesin->nomor_invoice ?? '-' }}</td>
            </tr>
            <tr>
                <td>Supplier/Vendor</td>
                <td>{{ $mesin->supplier ?? '-' }}</td>
            </tr>
            <tr>
                <td>Tanggal Berakhir Garansi</td>
                <td>{{ $mesin->tanggal_waranty_expired ? $mesin->tanggal_waranty_expired->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Umur Ekonomis</td>
                <td>{{ $mesin->umur_ekonomis_bulan ? $mesin->umur_ekonomis_bulan . ' bulan' : '-' }}</td>
            </tr>
            <tr>
                <td>Estimasi Penggantian</td>
                <td>{{ $mesin->estimasi_penggantian ? $mesin->estimasi_penggantian->format('d/m/Y') : '-' }}</td>
            </tr>
        </table>
    </div>

    <!-- Spesifikasi & Dokumentasi -->
    <div class="section">
        <div class="section-title">⚙️ SPESIFIKASI & DOKUMENTASI</div>
        <table class="info-table">
            <tr>
                <td>Spesifikasi Teknis</td>
                <td>{{ $mesin->spesifikasi_teknis ?? '-' }}</td>
            </tr>
            <tr>
                <td>Catatan Tambahan</td>
                <td>{{ $mesin->catatan ?? '-' }}</td>
            </tr>
            <tr>
                <td>Dokumen Pendukung</td>
                <td>{{ $mesin->dokumen_pendukung ?? '-' }}</td>
            </tr>
        </table>
    </div>

    @if($mesin->komponens->count() > 0)
    <div class="page-break"></div>
    
    <!-- Daftar Komponen -->
    <div class="section">
        <div class="section-title">🔧 DAFTAR KOMPONEN ({{ $mesin->komponens->count() }} Komponen)</div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Nama Komponen</th>
                    <th style="width: 12%;">Manufaktur</th>
                    <th style="width: 15%;">Part Number</th>
                    <th style="width: 15%;">Lokasi</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 10%;">Jadwal Ganti</th>
                    <th style="width: 13%;">Est. Ganti</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mesin->komponens as $index => $komponen)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $komponen->nama_komponen }}</td>
                    <td>{{ $komponen->manufacturer ?? '-' }}</td>
                    <td>{{ $komponen->part_number ?? '-' }}</td>
                    <td>{{ $komponen->lokasi_pemasangan ?? '-' }}</td>
                    <td class="text-center">
                        @php
                            $statusKomponenClass = match($komponen->status_komponen) {
                                'normal' => 'status-normal',
                                'perlu_ganti' => 'status-perlu-ganti',
                                'rusak' => 'status-rusak',
                                default => ''
                            };
                            $statusKomponenText = match($komponen->status_komponen) {
                                'normal' => '✅ Normal',
                                'perlu_ganti' => '⚠️ Perlu Ganti',
                                'rusak' => '❌ Rusak',
                                default => $komponen->status_komponen
                            };
                        @endphp
                        <span class="status-badge {{ $statusKomponenClass }}">{{ $statusKomponenText }}</span>
                    </td>
                    <td class="text-center">{{ $komponen->jadwal_ganti_bulan ? $komponen->jadwal_ganti_bulan . ' bln' : '-' }}</td>
                    <td class="text-center">{{ $komponen->estimasi_tanggal_ganti_berikutnya ? $komponen->estimasi_tanggal_ganti_berikutnya->format('d/m/Y') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($mesin->requests->count() > 0)
    <!-- Riwayat Maintenance -->
    <div class="section">
        <div class="section-title">📝 RIWAYAT MAINTENANCE REQUEST ({{ $mesin->requests->count() }} Request)</div>
        <table class="detail-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 15%;">No. Request</th>
                    <th style="width: 25%;">Deskripsi</th>
                    <th style="width: 12%;">Urgensi</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 15%;">Dibuat Oleh</th>
                    <th style="width: 16%;">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mesin->requests->take(20) as $index => $request)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $request->request_number }}</td>
                    <td>{{ \Str::limit($request->problema_deskripsi, 80) }}</td>
                    <td class="text-center">{{ ucfirst($request->urgency_level) }}</td>
                    <td class="text-center">{{ ucfirst($request->status) }}</td>
                    <td>{{ $request->creator?->name ?? '-' }}</td>
                    <td class="text-center">{{ $request->requested_at ? $request->requested_at->format('d/m/Y H:i') : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($mesin->requests->count() > 20)
        <div style="font-size: 8px; text-align: center; font-style: italic;">
            Menampilkan 20 request terbaru dari total {{ $mesin->requests->count() }} request
        </div>
        @endif
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        Dokumen dicetak pada {{ $tanggalCetak->format('d F Y H:i') }} WIB<br>
        © PT PARAMA BINA ENERGI - Sistem Manajemen Mesin
    </div>
</body>
</html>
