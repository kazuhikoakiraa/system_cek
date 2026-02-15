<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi Suku Cadang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 18px;
        }
        .header p {
            margin: 3px 0;
            color: #666;
        }
        .info-box {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 3px 5px;
        }
        .summary {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
        }
        .summary-box {
            background: #f0f0f0;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            flex: 1;
            margin: 0 5px;
        }
        .summary-box h3 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        .summary-box p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #4a5568;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 9px;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
        }
        .badge-in {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-out {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-return {
            background-color: #fff3cd;
            color: #856404;
        }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN TRANSAKSI SUKU CADANG</h2>
        <p>Periode: {{ isset($filters['dari_tanggal']) && $filters['dari_tanggal'] ? date('d/m/Y', strtotime($filters['dari_tanggal'])) : 'Semua' }} - {{ isset($filters['sampai_tanggal']) && $filters['sampai_tanggal'] ? date('d/m/Y', strtotime($filters['sampai_tanggal'])) : 'Semua' }}</p>
        <p>Dicetak: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-box">
        <table>
            <tr>
                <td width="20%"><strong>Total Transaksi</strong></td>
                <td width="30%">: {{ $transactions->count() }} transaksi</td>
                <td width="20%"><strong>Total Masuk</strong></td>
                <td width="30%">: {{ number_format($totalMasuk) }} unit</td>
            </tr>
            <tr>
                <td><strong>Total Keluar</strong></td>
                <td>: {{ number_format($totalKeluar) }} unit</td>
                <td><strong>Total Retur</strong></td>
                <td>: {{ number_format($totalRetur) }} unit</td>
            </tr>
        </table>
    </div>

    @if($transactions->count() > 0)
    <table>
        <thead>
            <tr>
                <th width="8%">No. Transaksi</th>
                <th width="10%">Tanggal</th>
                <th width="10%">Kode</th>
                <th width="20%">Nama Suku Cadang</th>
                <th width="7%">Tipe</th>
                <th width="7%">Jumlah</th>
                <th width="7%">Stok Sebelum</th>
                <th width="7%">Stok Sesudah</th>
                <th width="17%">Keterangan</th>
                <th width="7%">Oleh</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $transaction)
            <tr>
                <td>{{ $transaction->nomor_transaksi }}</td>
                <td>{{ $transaction->tanggal_transaksi->format('d/m/Y H:i') }}</td>
                <td>{{ $transaction->sparePart->kode_suku_cadang }}</td>
                <td>{{ $transaction->sparePart->nama_suku_cadang }}</td>
                <td>
                    <span class="badge badge-{{ $transaction->tipe_transaksi === 'IN' ? 'in' : ($transaction->tipe_transaksi === 'OUT' ? 'out' : 'return') }}">
                        {{ $transaction->tipe_transaksi === 'IN' ? 'MASUK' : ($transaction->tipe_transaksi === 'OUT' ? 'KELUAR' : 'RETUR') }}
                    </span>
                </td>
                <td>{{ number_format($transaction->jumlah) }} {{ $transaction->sparePart->satuan }}</td>
                <td>{{ number_format($transaction->stok_sebelum) }}</td>
                <td>{{ number_format($transaction->stok_sesudah) }}</td>
                <td>{{ $transaction->keterangan ?? '-' }}</td>
                <td>{{ $transaction->user->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div class="no-data">
        <p>Tidak ada data transaksi untuk periode yang dipilih</p>
    </div>
    @endif

    <div class="footer">
        <p>Laporan ini digenerate secara otomatis oleh sistem</p>
    </div>
</body>
</html>
