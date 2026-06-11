<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>SPPK - KB Bank</title>
    <style>
        /* === CSS Reset & Dasar === */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4;
            margin: 1mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 8pt;
            line-height: 1.3;
            color: #000000;
            background: white;
            margin: 0;
            padding: 0;
        }

        .page {
            margin: 0;
            background: white;
            padding: 20mm 20mm 20mm 18mm;
        }

        /* === HEADER LOGO === */
        .header-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .header-table td {
            vertical-align: top;
            padding: 0;
        }
        .logo-left {
            width: 50%;
            text-align: left;
        }
        .logo-right {
            width: 50%;
            text-align: right;
        }
        .logo-img-left {
            height: 50px;
            width: auto;
            max-width: 230px;
        }
        .logo-img-right {
            height: 110px;
            width: auto;
            max-width: 260px;
            display: block;
            margin-left: auto;
            object-fit: contain;
            object-position: right center;
        }

        /* === NOMOR SURAT === */
        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        /* === JUDUL & PARAGRAF === */
        .title {
            font-weight: bold;
            margin: 12px 0 6px 0;
            text-decoration: underline;
            font-size: 10pt;
        }

        p {
            margin-bottom: 8px;
            text-align: justify;
            line-height: 1.35;
        }

        /* === TABEL DETAIL KREDIT === */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            table-layout: fixed;
        }

        .detail-table td {
            vertical-align: top;
            padding: 3px 0;
            word-wrap: break-word;
            line-height: 1.3;
        }

        /* Lebar kolom disesuaikan agar proporsional */
        .detail-table td:first-child {
            width: 6%;
            padding-right: 4px;
        }
        .detail-table td:nth-child(2) {
            width: 30%;
        }
        .detail-table td:nth-child(3) {
            width: 3%;
            text-align: left;
        }
        .detail-table td:last-child {
            width: 61%;
        }

        /* === TANDA TANGAN === */
        .signature-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
        }
        .signature-table td {
            vertical-align: top;
            width: 50%;
            padding-top: 15px;
        }
        .right {
            text-align: right;
        }
        .sig-gap {
            height: 70px;
        }
        .nama-ttd-debitur {
            font-weight: bold;
            margin-top: 5px;
        }
        .materai-info {
            font-size: 9pt;
            margin-top: 15px;
        }

        /* === UTILITY === */
        .text-bold {
            font-weight: bold;
        }
        .mb-2 {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- HEADER LOGO -->
        <table class="header-table">
            <tr>
                <td class="logo-left">
                    <img src="file://{{ str_replace('\\', '/', storage_path('upload/logo_kb.png')) }}" class="logo-img-left" alt="KB Bank Logo">
                </td>
                <td class="logo-right">
                    <img src="file://{{ str_replace('\\', '/', storage_path('upload/Logo_NBP.png')) }}" class="logo-img-right" alt="Koperasi Nata Buana Pasundan Logo">
                </td>
            </tr>
        </table>

        <!-- NOMOR SURAT -->
        <table class="meta-table">
            <tr>
                <td style="width: 35px;">No</td>
                <td style="width: 15px;">:</td>
                <td style="font-weight: normal;">{{ $nomor_sppk ?? '..................../SPPK/......../......../20....' }}</td>
            </tr>
        </table>

        <!-- ALAMAT PENERIMA -->
        <div style="margin-bottom: 20px;">
            <p>Kepada Yth,<br>
            Bapak/Ibu <strong>{{ $nama_debitur ?? '........................' }}</strong><br>
            {{ $alamat ?? 'Jl. ..........................................' }}<br>
            {{ $desa_kab_kota ?? 'Ds………./Kab/Kota………..' }} &nbsp;&nbsp; Kode Pos {{ $kode_pos ?? '.....' }}
            </p>
        </div>

        <!-- PERIHAL -->
        <p class="title">Perihal: Persetujuan Pemberian Kredit</p>

        <!-- PARAGRAF 1 -->
        <p>
            Untuk dan atas nama PT Bank KB Indonesia Tbk. ("KB Bank"), dengan ini kami selaku
            wakil/kuasa dan Mitra Channeling dari KB Bank, menyampaikan hal sebagai berikut:
        </p>

        <!-- PARAGRAF 2 -->
        <p>
            Menunjuk surat saudara/i tanggal {{ $tanggal_surat ?? '…/…/….' }} perihal permohonan kredit, dengan ini kami
            beritahukan bahwa permohonan kredit saudara/i, pada prinsipnya telah disetujui oleh KB Bank
            selaku pemberi kredit, dengan perincian sebagai berikut:
        </p>

        <!-- TABEL DETAIL KREDIT -->
        <table class="detail-table">
            <tr><td>1.</td><td>Plafond Kredit</td><td>:</td><td>{{ $plafond_kredit ?? 'Rp. ...............................' }}</td></tr>
            <tr><td>2.</td><td>Jangka Waktu</td><td>:</td><td>{{ $jangka_waktu ?? '............................' }} Bulan</td></tr>
            <tr><td>3.</td><td>Suku Bunga</td><td>:</td><td>{{ $suku_bunga ?? '.................' }}% Effectif p.a</td></tr>
            <tr><td>4.</td><td>Jenis Fasilitas</td><td>:</td><td>Kredit Konsumtif</td></tr>
            <tr><td>5.</td><td>Bentuk Fasilitas</td><td>:</td><td>Installment</td></tr>
            <tr><td>6.</td><td>Biaya Provisi</td><td>:</td><td>{{ $biaya_provisi ?? 'Rp. ...............................' }}</td></tr>
            <tr><td>7.</td><td>Biaya Administrasi Kredit</td><td>:</td><td>{{ $biaya_administrasi ?? 'Rp. ...............................' }}</td></tr>
            <tr><td>8.</td><td>Asuransi Jiwa Kredit</td><td>:</td><td>{{ $asuransi_jiwa ?? 'Rp. ...............................' }}</td></tr>
            <tr><td>9.</td><td>Materai</td><td>:</td><td>{{ $materai ?? 'Rp. ...............................' }}</td></tr>
            <tr><td>10.</td><td>Biaya Flagging</td><td>:</td><td>{{ $biaya_flagging ?? 'Rp. ...............................' }}</td></tr>
            <tr><td>11.</td><td>Total Biaya</td><td>:</td><td>{{ $total_biaya ?? 'Rp. ...............................' }}</td></tr>
            <tr><td>12.</td><td>Angsuran Dibayar Dimuka</td><td>:</td><td>{{ $angsuran_dimuka ?? 'Rp. ...............................' }}</td></tr>
            <tr><td>13.</td><td>Total Penerimaan</td><td>:</td><td>{{ $total_penerimaan ?? 'Rp. ...............................' }}</td></tr>
            <tr><td>14.</td><td>Angsuran (Pokok + Bunga) Perbulan</td><td>:</td><td>{{ $angsuran_perbulan ?? 'Rp. ...............................' }}</td></tr>
            <tr><td>15.</td><td>Biaya Administrasi Angsuran Perbulan</td><td>:</td><td>{{ $biaya_adm_angsuran ?? 'Rp. ...............................' }}</td></tr>
            <tr>
                <td>16.</td>
                <td>Cara Pembayaran</td>
                <td>:</td>
                <td>
                    Manfaat pensiun saudara/i setiap bulan dipotong sebesar Terbilang :<br>
                    {{ $angsuran_terbilang ?? 'Rp. ........................,00 (.......................................................................Rupiah)' }}
                </td>
            </tr>
            <tr>
                <td>17.</td>
                <td>Jaminan</td>
                <td>:</td>
                <td>
                    a. Asli Surat Pernyataan kuasa Potong Gaji dari Debitur tertanggal : {{ $tgl_surat_kuasa ?? '…./…./….' }} atas nama : {{ $nama_debitur ?? '.............' }}<br>
                    b. Asli Surat Keputusan (SK) Pensiunan Nomor : {{ $no_sk_pensiun ?? '..........................' }}<br>
                    &nbsp;&nbsp;&nbsp;Tertanggal : {{ $tgl_sk_pensiun ?? '…./…./….' }} atas nama {{ $nama_debitur ?? '.....................' }}<br>
                    c. Asli/copy Bukti Sertifikat Kepesertaan Asuransi Jiwa Kredit atas nama {{ $nama_debitur ?? '..........................' }}
                </td>
            </tr>
            <tr>
                <td>18.</td>
                <td>Pelunasan Dipercepat</td>
                <td>:</td>
                <td>
                    a. Debitur akan dikenakan denda/penalti sebesar 10% dari sisa Outstanding Kredit dan wajib mengganti biaya lainnya dikecualikan untuk Top Up kredit.<br>
                    b. Denda keterlambatan angsuran dikenakan sebesar 4% dari nilai angsuran (pokok+bunga).
                </td>
            </tr>
        </table>

        <!-- PARAGRAF PENUTUP -->
        <p>
            Hal-hal lain yang belum diatur dalam SPPK ini, akan diatur dan ditentukan kemudian di dalam Perjanjian Kredit.
        </p>

        <p>
            Demikian kami sampaikan. Apabila saudara/i setuju dengan ketentuan di atas, maka sebagai bukti persetujuan,
            saudara/i diminta untuk menandatangani dan selanjutnya mengembalikan kepada kami.
        </p>

        <p style="margin-top: 5px;">
            {{ $kota_ttd ?? '............' }}, {{ $tgl_ttd ?? '…./…./….' }}
        </p>

        <!-- TABEL TANDA TANGAN -->
        <table class="signature-table">
            <tr>
                <td>
                    <p>Debitur</p>
                    <div class="sig-gap"></div>
                    <p class="nama-ttd-debitur">{{ $nama_ttd_debitur ?? '...............................' }}</p>
                    <div class="materai-info">Materai 10.000</div>
                </td>
                <td class="right">
                    <p>Kreditur<br>
                    KOPERASI NATA BUANA PASUNDAN<br>
                    Untuk dan atas nama KB Bank</p>
                    <div class="sig-gap"></div>
                    <p>( ..........................…………………… )<br>
                    ({{ $nama_kuasa_kb_bank ?? '…………' }})<br>
                    (selaku kuasa KB Bank)</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>