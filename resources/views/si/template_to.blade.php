<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SI - Take Over</title>
    <style>
        @page {
            margin: 28mm 34mm 28mm 34mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .page {
            width: auto;
            padding: 0 12mm;
            page-break-after: auto;
            page-break-inside: auto;
        }

        .header-table {
            width: 100%;
            margin-bottom: 10px;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo-left {
            width: 150px;
        }

        .logo-right {
            width: 165px;
            text-align: right;
        }

        .logo-img-left {
            height: 58px;
            width: auto;
        }

        .logo-img-right {
            height: 84px;
            width: auto;
        }

        p {
            margin-bottom: 8px;
            text-align: justify;
            overflow-wrap: anywhere;
        }

        .subject {
            margin: 10px 0 12px;
        }

        .subject td {
            vertical-align: top;
            padding: 1px 0;
        }

        .subject td:first-child {
            width: 68px;
        }

        .subject td:nth-child(2) {
            width: 10px;
        }

        .detail {
            width: 100%;
            margin: 6px 0;
        }

        .detail td {
            vertical-align: top;
            padding: 1px 0;
        }

        .detail td:first-child {
            width: 170px;
        }

        .detail td:nth-child(2) {
            width: 10px;
        }

        ol {
            padding-left: 18px;
            margin: 5px 0 8px;
        }

        li {
            margin-bottom: 4px;
            text-align: justify;
            overflow-wrap: anywhere;
        }

        .signature {
            margin-top: 22px;
            width: 100%;
        }

        .signature td {
            text-align: left;
            vertical-align: top;
        }

        .sig-space {
            height: 65px;
        }
    </style>
</head>
<body>
<div class="page">
    <table class="header-table">
        <tr>
            <td class="logo-left">
                <img src="file://{{ str_replace('\\', '/', storage_path('upload/logo_kb.png')) }}" class="logo-img-left" alt="KB Bank">
            </td>
            <td></td>
            <td class="logo-right">
                <img src="file://{{ str_replace('\\', '/', storage_path('upload/logo_nbp.png')) }}" class="logo-img-right" alt="NBP">
            </td>
        </tr>
    </table>

    <p>KOPERASI NATA BUANA PASUNDAN</p>
    <p>{{ ($kota_ttd ?? 'Bandung') . ', ' . ($tanggal_surat ?? '...') }}</p>

    <table class="subject">
        <tr><td>No</td><td>:</td><td>{{ $nomor_si }}</td></tr>
        <tr><td>Perihal</td><td>:</td><td>{{ $perihal }}</td></tr>
    </table>

    <p>Kepada Yth,</p>
    <p>PT. Bank KB Indonesia, Tbk</p>
    <p>Up. {{ $attention_name }}</p>

    <p>Dengan hormat,</p>

    <p>
        Sesuai dengan Perjanjian Kerjasama Penerus Pinjaman yang telah ditandatangani antara PT. Bank KB Indonesia,
        Tbk (BANK) dan KSP Nata Buana Pasundan (KOPERASI) nomor {{ $nomor_pks_nbp }} dan {{ $nomor_pks_kb }}
        tanggal {{ $tanggal_pks }} (selanjutnya disebut "Perjanjian") dengan ini kami mengajukan pencairan kredit
        dengan rincian sebagai berikut:
    </p>

    <table class="detail">
        <tr><td>Plafond Kredit</td><td>:</td><td>{{ $plafond }} {{ $plafond_terbilang }}</td></tr>
        <tr><td>Jumlah debitur</td><td>:</td><td>{{ $jumlah_debitur }} (Data Terlampir)</td></tr>
        <tr><td>Produk</td><td>:</td><td>{{ $produk }}</td></tr>
        <tr><td>Jenis Kredit</td><td>:</td><td>{{ $jenis_kredit_label }}</td></tr>
    </table>

    <p>Selanjutnya kami mohon:</p>
    <ol>
        <li>Pencairan kredit bagi debitur KSP Nata Buana Pasundan dapat dicairkan sebesar {{ $total_pencairan }} ke rekening escrow {{ $rekening_ksp_nama }} nomor {{ $rekening_escrow_no }} an. {{ $rekening_ksp_nama }}.</li>
        <li>Pemindahbukuan biaya flagging ke rekening nomor {{ $rekening_flagging_no }} an. {{ $rekening_ksp_nama }} dengan data terlampir.</li>
        <li>Pemindahbukuan biaya asuransi dengan keterangan transaksi ({{ $rekening_ksp_nama }}_{{ $nama_debitur }}) dari rekening escrow nomor {{ $rekening_escrow_no }} an. {{ $rekening_ksp_nama }} ke rekening Heksa Insurance nomor {{ $rekening_asuransi_no }} an. {{ $nama_asuransi }} dengan data terlampir.</li>
        <li>Selanjutnya dipindahbukukan ke rekening debitur sesuai dengan data terlampir.</li>
        <li>Harap dilakukan pemblokiran dana takeover pada rekening debitur sesuai data terlampir.</li>
        <li>Harap dilakukan pemindahbukuan dana Takeover dari rekening KSP Nata Buana ke rekening debitur dengan nomor rekening {{ $rekening_penerima }}.</li>
    </ol>

    <table class="signature">
        <tr>
            <td>
                Hormat kami,<br>
                Ketua KSP Nata Buana Pasundan
                <div class="sig-space"></div>
                (Sutrisno KP)
            </td>
        </tr>
    </table>
</div>
</body>
</html>