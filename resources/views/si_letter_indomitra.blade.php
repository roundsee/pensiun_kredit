<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Permohonan Pencairan</title>
    <style>
        body {
            width: 190mm;
            height: 297mm;
            margin: 1mm;
            padding: 1mm;
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.2;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: auto;
        }
        .header {
            text-align: center;
            font-weight: bold;
        }
        .sub-header {
            text-align: center;
            font-size: 12px;
        }
        .text-right {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature {
            margin-top: 40px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            KOPERASI NATA BUANA PASUNDAN
        </div>
        <div class="sub-header">
            AKTA NO: 00908/BH/M.KUKM.2/X/2018 <br>
            RUKO BUSINESS PARK No. 16 <br>
            Taman Kopo Indah II, Margaasih, Kab. Bandung
        </div>

        <p class="text-right">Bandung, {{\Carbon\Carbon::parse($pengajuan->tgl_pk)->format("Y-m-d")}}</p>

        <p>No :</p>
        <p>Perihal :</p>

        <p>Kepada,<br>
        Direktur Operasional BPR Indomitra Artha Pertiwi<br>
        Di tempat</p>

        <div>Dengan Hormat,</div>

        <p>Sehubungan dengan perjanjian Kerja Sama dalam rangka pemberian fasilitas kredit <i>channeling</i> antara PT BPR Indomitra Artha Pertiwi dengan Koperasi Nata Buana Pasundan, dengan ini kami mohon agar dapat dicairkan ke rekening escrow kami di:</p>
        <div><b>Nama Bank</b> : MNC BANK</div>
        <div><b>Nomor Rekening</b> : 100 01 089004059 3</div>
        <p><b>Nama Rekening</b> : KOPERASI SIMPAN PINJAM NATA BUANA PASUNDAN</p>

        <table>
            <tr>
                <td>Pada tanggal</td>
                <td>: {{\Carbon\Carbon::parse($pengajuan->tgl_pk)->format("Y-m-d")}}</td>
            </tr>
            <tr>
                <td>Nama Debitur</td>
                <td>: {{$mohon->nama_ktp}}</td>
            </tr>
            <tr>
                <td>Plafond</td>
                <td>: <b>Rp. {{number_format($mohon->plafon)}}</b></td>
            </tr>
            <tr>
                <td>Keterangan</td>
                <td>: Usia Platinum</td>
            </tr>
        </table>



        <p>Hormat kami,</p>

        <div class="signature">
            <p><b>Sutrisno KP</b><br>Ketua</p>
        </div>
    </div>

</body>
</html>
