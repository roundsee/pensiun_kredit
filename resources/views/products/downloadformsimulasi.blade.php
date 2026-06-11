<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulasi Pembiayaan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 20px;
        }
        .container {
            width: 100%;
            margin: auto;
        }
        .header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .header div:nth-child(1) {
            font-size: 18px;
        }
        .header div:nth-child(2) {
            font-size: 16px;
            color: green;
        }
        .header div:nth-child(3) {
            font-size: 12px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 10px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 5px;
            text-align: left;
            vertical-align: top;
        }
        .table th {
            font-weight: bold;
            text-transform: uppercase;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid black;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .warning {
            background-color: yellow;
            font-weight: bold;
            text-align: center;
            padding: 2px 5px;
        }
        .highlight {
            background-color: #f1f1f1;
            font-weight: bold;
        }
        .right-align {
            text-align: right;
        }
    </style>
</head>
<body>
    @php
        $input = $input ?? [];
        $result = $result ?? [];
        $format = fn($value) => number_format((float) ($value ?? 0), 0, ',', '.');
    @endphp

    <div class="container">
        <div class="header">
            <img src="/img/Logo_nata.jpeg" alt="NBP logo" width="80" height="0" class="mr-4">
            <img src="/img/Logo_nata.jpeg" alt="NBP logo" width="80" height="0" class="mr-4">
            <div>SIMULASI PEMBIAYAAN</div>
            <div>KOPERASI NATA BUANA PASUNDAN</div>
            <div>Versi 21.112024</div>
        </div>

        <div class="section">
            <div class="section-title">INPUT DATA</div>

            <div>{{ $input['kode_area'] ?? '-' }}, <b>{{ $generatedAt->format('d-m-Y H:i') }}</b></div>
            <table class="table">
                <tr>
                    <td><b>NO PENSIUN:</b></td>
                    <td>{{ $input['nomor_pensiun'] ?? '-' }}</td>
                    <td><b>KODE_SDA:</b></td>
                    <td><b>NBP</b></td>
                </tr>
                <tr>
                    <td><b>NAMA DEBITUR:</b></td>
                    <td>{{ $input['nama_debitur'] ?? '-' }}</td>
                    <td><b>INSTANSI:</b></td>
                    <td>{{ strtoupper((string) ($input['instansi'] ?? '-')) }}</td>
                </tr>
                <tr>
                    <td><b>TANGGAL LAHIR:</b></td>
                    <td>{{ $input['tanggal_lahir'] ?? '-' }}</td>
                    <td><b>USIA SAAT INI:</b></td>
                    <td>{{ $result['umur_text'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td><b>PRODUK:</b></td>
                    <td>{{ $input['produk'] ?? '-' }}</td>
                    <td><b>MAX TENOR_PLATINUM:</b></td>
                    <td>{{ $result['tenor_max'] ?? 0 }} Bulan</td>
                </tr>
                <tr>
                    <td><b>PENERIMA PENSIUN:</b></td>
                    <td>{{ $input['jenis_pensiun'] ?? '-' }}</td>
                    <td><b>MAX TENOR_MIKRO:</b></td>
                    <td>{{ $result['tenor'] ?? 0 }} Bulan</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">REKOMENDASI</div>
            <table class="table table-bordered">
                <tr class="highlight">
                    <td><b>BANK ASAL:</b></td>
                    <td>{{ $input['bank_tujuan'] ?? '-' }}</td>
                    <td><b>PLAFOND PENGAJUAN:</b></td>
                    <td>Rp {{ $format($result['plafond'] ?? 0) }} <span class="success">[v] SUCCESS</span></td>
                </tr>
                <tr>
                    <td><b>PEMBAYARAN:</b></td>
                    <td>{{ $input['pelunasan'] ?? '0' }}</td>
                    <td><b>ANGSURAN:</b></td>
                    <td>Rp {{ $format($result['angsuran'] ?? 0) }} <span class="success">[v] SUCCESS</span></td>
                </tr>
                <tr>
                    <td><b>GAJI PENSIUN:</b></td>
                    <td>Rp {{ $format($result['gaji_pensiun'] ?? 0) }}</td>
                    <td><b>SISA GAJI AKHIR:</b></td>
                    <td>Rp {{ $format($result['sisa_gaji_akhir'] ?? 0) }} <span class="success">[v] SUCCESS</span></td>
                </tr>
                <tr>
                    <td><b>SISA GAJI:</b></td>
                    <td>Rp {{ $format($result['sisa_gaji_akhir'] ?? 0) }}</td>
                    <td><b>TENOR PENGAJUAN:</b></td>
                    <td>{{ $result['tenor'] ?? 0 }} Bulan</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">INFORMASI</div>
            <table class="table">
                <tr>
                    <td><b>TGL PERMOHONAN:</b></td>
                    <td>{{ $input['tanggal_simulasi'] ?? $generatedAt->format('d-m-Y') }}</td>
                    <td><b>NO.PENSIUN:</b></td>
                    <td>{{ $input['nomor_pensiun'] ?? '-' }}</td>
                </tr>
                <tr>
                    <td><b>NAMA DEBITUR:</b></td>
                    <td>{{ $input['nama_debitur'] ?? '-' }}</td>
                    <td><b>PLAFOND PENGAJUAN:</b></td>
                    <td>Rp {{ $format($result['plafond'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td><b>TENOR:</b></td>
                    <td>{{ $result['tenor'] ?? 0 }} Bulan</td>
                    <td><b>ANGSURAN:</b></td>
                    <td>Rp {{ $format($result['angsuran'] ?? 0) }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">BIAYA-BIAYA</div>
            <table class="table table-bordered">
                <tr>
                    <td><b>PROVISI:</b></td>
                    <td>Rp {{ $format($result['provisi'] ?? 0) }}</td>
                    <td><b>BIAYA TAKE OVER:</b></td>
                    <td>Rp {{ $format($result['takeover_fee'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td><b>ADMINISTRASI:</b></td>
                    <td>Rp {{ $format($result['administrasi'] ?? 0) }}</td>
                    <td><b>BIAYA MATERAI:</b></td>
                    <td>Rp {{ $format($result['materai'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td><b>ASURANSI:</b></td>
                    <td>Rp {{ $format($result['asuransi'] ?? 0) }}</td>
                    <td><b>BIAYA LAIN:</b></td>
                    <td>Rp {{ $format($result['extra_premi'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td><b>BLOKIR ANGSURAN:</b></td>
                    <td>Rp {{ $format($result['amount_blokir_angsuran'] ?? 0) }}</td>
                    <td><b>BIAYA FLAGGING:</b></td>
                    <td>Rp {{ $format($result['tata_laksana'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td><b>RETENSI 1X ANGSURAN:</b></td>
                    <td>Rp {{ $format($result['amount_blokir_angsuran'] ?? 0) }}</td>
                    <td><b>TOTAL BIAYA:</b></td>
                    <td>Rp {{ $format($result['total_biaya'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td><b>TERIMA BERSIH:</b></td>
                    <td colspan="3">Rp {{ $format($result['terima_bersih'] ?? 0) }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
