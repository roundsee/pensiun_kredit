<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Perjanjian Kredit KB</title>
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

        .page:last-child {
            page-break-after: avoid;
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

        h1 {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 4px;
        }

        h2 {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 14px 0 6px;
        }

        p {
            margin-bottom: 8px;
            text-align: justify;
            overflow-wrap: anywhere;
        }

        .pasal-title {
            text-align: center;
            font-weight: bold;
            margin: 12px 0 4px;
        }

        ol.roman { list-style: upper-roman; padding-left: 20px; margin-bottom: 8px; }
        ol.alpha  { list-style: lower-alpha; padding-left: 30px; margin-bottom: 8px; }
        ol.num    { list-style: decimal;     padding-left: 20px; margin-bottom: 8px; }

        li {
            margin-bottom: 5px;
            text-align: justify;
            overflow-wrap: anywhere;
        }

        td {
            overflow-wrap: anywhere;
        }

        table {
            width: 100%;
            table-layout: fixed;
        }

        .field-row {
            width: 100%;
            margin-bottom: 2px;
        }

        .field-label {
            display: inline-block;
            width: 240px;
            vertical-align: top;
        }

        .field-colon {
            display: inline-block;
            width: 10px;
        }

        .field-value {
            display: inline-block;
        }

        .ttd-table {
            width: 100%;
            margin-top: 30px;
        }

        .ttd-table td {
            text-align: center;
            vertical-align: top;
            width: 50%;
            padding: 0 10px;
        }

        .ttd-box {
            height: 70px;
        }

        .underline { text-decoration: underline; }
        .bold { font-weight: bold; }
        .center { text-align: center; }

        .blank {
            border-bottom: 1px dotted #000;
            display: inline;
            min-width: 0;
            max-width: 100%;
            vertical-align: baseline;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .template-version {
            font-size: 9pt;
            color: #666;
            text-align: right;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

{{-- ═══════════════════════════ HALAMAN 1 ═══════════════════════════ --}}
<div class="page">
    <div class="template-version">Template: Perjanjian Kredit - KB Version</div>

    {{-- Header --}}
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

    <h1>PERJANJIAN KREDIT</h1>
    <p class="center">No : {{ $no_pk }}</p>

    <p>
        Perjanjian Kredit ini (selanjutnya disebut "<strong>Perjanjian</strong>") dibuat di
        <span class="blank">{{ $kota_kab }}</span> pada hari ini
        <span class="blank">{{ $hari_ttd }}</span>,
        tanggal {{ $tgl_ttd }} oleh dan antara :
    </p>

    <ol class="roman">
        <li>
            (Nama Debitur <span class="blank">{{ $nama_debitur }}</span>),
            pemegang Kartu Tanda Penduduk (KTP) No.
            <span class="blank">{{ $no_ktp }}</span>,
            bertempat tinggal di (Alamat <span class="blank">{{ $alamat }}</span>),
            RT <span class="blank">{{ $rt }}</span>,
            RW <span class="blank">{{ $rw }}</span>,
            Desa/Kel. <span class="blank">{{ $desa_kel }}</span>,
            Kec. <span class="blank">{{ $kecamatan }}</span>,
            Kota/Kabupaten <span class="blank">{{ $kota_kab }}</span>,
            (Kode Pos {{ $kode_pos }}),
            bertindak untuk dan atas nama diri sendiri. (selanjutnya disebut "<strong>Debitur</strong>").
        </li>
        <li>
            PT Bank KB Indonesia Tbk suatu Perseroan terbatas berkedudukan di Jakarta, dalam hal ini
            diwakili oleh <span class="blank">{{ $nama_perwakilan_kb }}</span> dalam jabatannya sebagai
            <span class="blank">{{ $jabatan }}</span> di <span class="blank">Koperasi Nata Buana Pasundan</span>,
            dalam hal ini bertindak selaku kuasa PT Bank KB Indonesia Tbk berdasarkan Surat Kuasa dari
            <span class="blank">Koperasi Nata Buana Pasundan</span>, Nomor SKU.0030/PDI/I/2026
             tanggal 02/02/2026
            jo. Surat Kuasa Substitusi dari PT Bank KB Indonesia Tbk kepada
            <span class="blank">Koperasi Nata Buana Pasundan</span> Nomor NBP.01.0030/PIC/II/2026
             tanggal  02/02/2026 no. 040/PKS-NBP/I/2026 dan no. PKS.001/CPA II/II/2026.
            Perjanjian Kerjasama Penerusan Pinjaman (Channeling) tertanggal 02/01/2026. 
            berikut perubahan, perpanjangan dan penambahannya, dari dan karenanya sa dan berwenanng untuk bertindak dan atas nama serta mewakili KB Bank 
            (selanjutnya disebut "<strong>Kreditur</strong>").
        </li>
    </ol>

    <p>Debitur dan Kreditur selanjutnya secara bersama - sama disebut "<strong>Para Pihak</strong>".</p>

    <p>Para Pihak menerangkan dan menyatakan hal-hal sebagai berikut:</p>

    <ol class="num">
        <li>
            Bahwa Debitur sebelumnya mengajukan permohonan kredit/pinjaman kepada PT Bank KB
            Indonesia Tbk, melalui Koperasi Nata Buana Pasundan.
        </li>
        <li>
            Bahwa selanjutnya PT Bank KB Indonesia Tbk, pada prinsipnya telah menyetujui permohonan 
            kredit/pinjaman yaitu berdasarkan Surat Persetujuan Pemberian Kredit tertanggal 
            <span class="blank">{{ $tanggal_sppk }}</span> (SPPK) yang dalam penerbitan SPPK tersebut 
            diwakili oleh Nata Buana Pasundan selaku kuasa PT Bank KB Indonesia Tbk. 
        </li>
        <li>
            Bahwa PT Bank KB Indonesia Tbk, selaku Kreditur, dan Debitur hendak menuangkan
            pemberian kredit tersebut ke dalam suatu perjanjian kredit yaitu sebagaimana dalam Perjanjian
            ini.
        </li>
    </ol>

    <p>
        Berdasarkan hal tersebut di atas, Para Pihak selanjutnya telah setuju dan sepakat untuk
        membuat dan menandatangani Perjanjian ini dengan syarat dan ketentuan sebagai berikut :
    </p>

    <p class="pasal-title">Pasal 1<br>Fasilitas Kredit</p>

    <ol class="num">
        <li>
            Atas permohonan Debitur, Kreditur setuju memberikan fasilitas kredit kepada Debitur
            dengan ketentuan :
            <table style="width:100%; margin: 6px 0 6px 20px;">
                <tr><td style="width:220px;">a. Plafond Kredit</td><td>: <strong>{{ $plafond_kredit }}</strong></td></tr>
                <tr><td>b. Jangka Waktu</td><td>: <strong>{{ $jangka_waktu }}</strong> Bulan</td></tr>
                <tr><td>c. Suku Bunga</td><td>: <strong>{{ $suku_bunga }}</strong>% Effectif p.a</td></tr>
                <tr><td>d. Jenis Fasilitas</td><td>: Kredit Konsumtif</td></tr>
                <tr><td>e. Bentuk Fasilitas</td><td>: Installment</td></tr>
                <tr><td>f. Biaya Provisi</td><td>: <strong>{{ $biaya_provisi }}</strong></td></tr>
                <tr><td>g. Biaya Administrasi Kredit</td><td>: <strong>{{ $biaya_administrasi}}</strong></td></tr>
                <tr><td>h. Asuransi Jiwa Kredit</td><td>: <strong>{{ $asuransi_jiwa }}</strong></td></tr>
            </table>
        </li>
    </ol>
</div>

{{-- ═══════════════════════════ HALAMAN 2 ═══════════════════════════ --}}
<div class="page">
    <table style="width:100%; margin: 6px 0 16px 20px;">
        <tr><td style="width:220px;">i. Materai</td><td>: <strong>{{ $materai }}</strong></td></tr>
        <tr><td>j. Biaya Flagging</td><td>: <strong>{{ $biaya_flagging }}</strong></td></tr>
        <tr><td>k. Total Biaya</td><td>: <strong>{{ $total_biaya }}</strong></td></tr>
        <tr><td>l. Angsuran Dibayar Dimuka</td><td>: <strong>{{ $angsuran_dimuka }}</strong></td></tr>
        <tr><td>m. Total Penerimaan (Dana Yang Diterima)</td><td>: <strong>{{ $total_penerimaan }}</strong></td></tr>
        <tr><td>n. Angsuran (Pokok + Bunga) Perbulan</td><td>: <strong>{{ $angsuran_perbulan }}</strong></td></tr>
        <tr><td>o. Biaya Administrasi Angsuran Perbulan</td><td>: <strong>{{ $biaya_adm_angsuran }}</strong></td></tr>
    </table>

    <ol class="num" start="2">
        <li>
            Dalam hal terjadi perubahan suku bunga yang menambah biaya Debitur sebagaimana
            dimaksud pada pasal 1 ayat 1 huruf (c) diatas, maka Debitur sepakat bahwa perubahan
            tersebut akan berlaku dan mengikat kepada Debitur, dengan cukup diberitahukan secara
            tertulis oleh Kreditur kepada Debitur.
        </li>
    </ol>

    <p class="pasal-title">Pasal 2<br>Jangka Waktu dan Jadwal Angsuran</p>

    <ol class="num">
        <li>
            Jangka waktu fasilitas kredit <strong>{{ $jangka_waktu }}</strong> bulan terhitung sejak tanggal
            <span class="blank">{{ $tgl_mulai }}</span>
            dan akan berakhir pada tanggal <span class="blank">{{ $tgl_lunas }}</span>.
        </li>
        <li>
            Tiap bulannya, Debitur wajib membayar biaya Angsuran per bulan (sebagaimana huruf n
            angka 1) ditambah dengan biaya administrasi angsuran per bulan (sebagaimana pada huruf
            o angka 1), sehingga total sebesar <strong>{{ $angsuran_dimuka }}</strong>
            (<span class="blank">{{ $angsuran_terbilang }}</span>) / bulan sesuai dengan jadwal angsuran
            yang telah disepakati Para Pihak.
        </li>
        <li>
            Pembayaran angsuran dilakukan dalam <strong>{{ $angsuran_total_bulan }}</strong> kali angsuran
            yang harus dibayar sesuai dengan tanggal pembayaran manfaat pensiun/ gaji karyawan
            tiap bulannya untuk selanjutnya dilakukan pembayaran kewajiban pada bulan berkenaan
            dan harus sudah lunas selambat-lambatnya tanggal <span class="blank">{{ $tgl_lunas }}</span>.
        </li>
        <li>
            Dalam hal Debitur terlambat melakukan pembayaran dari tanggal yang telah
            ditentukan/ditetapkan, maka Debitur akan dikenakan Denda Keterlambatan
            pembayaran angsuran sebesar 4% (Empat Persen) perbulan. Denda Keterlambatan
            dimaksud harus dibayar dengan seketika dan sekaligus lunas bersamaan dengan
            pembayaran angsuran yang tertunggak.
        </li>
        <li>
            Pelunasan dipercepat akan dikenakan denda/penalti sebesar 10% dari sisa Outstanding
            Kredit dan wajib mengganti biaya lainnya, dikecualikan untuk Top Up kredit.
        </li>
        <li>
            Pelaksanaan pelunasan hanya dapat dilakukan dari tanggal 1 sampai dengan tanggal
            10 tiap bulannya. Apabila diatas tanggal 10 tiap bulannya, maka pelunasan baru akan
            dilakukan/direalisasikan dalam bulan berikutnya.
        </li>
        <li>
            Apabila tanggal batas waktu pembayaran kewajiban yang harus dilakukan Debitur
            kepada Kreditur jatuh bukan pada Hari Kerja (hari Senin sampai dengan hari Jumat di
            luar hari Libur yang ditetapkan oleh Pemerintah, dimana Bank beroperasi), maka
            pembayaran harus dilakukan oleh Debitur pada 1 (satu) hari kerja sebelumnya.
        </li>
    </ol>

    <p class="pasal-title">Pasal 3<br>Penarikan Fasilitas Kredit Dan Pengakuan Hutang</p>

    <ol class="num">
        <li>
            Debitur bersedia membuka rekening tabungan Siaga Pensiun di PT. Bank KB Indonesia, Tbk
        </li>
        <li>
            Penarikan fasilitas Kredit yang diberikan Kreditur kepada Debitur dicairkan sekaligus, yaitu
            sebesar Dana Yang Diterima (sebagaimana tersebut pada huruf m).
        </li>
        <li>
            Penandatanganan Perjanjian ini merupakan tanda penerimaan yang sah atas seluruh jumlah 
            hutang pokok sebagaimana dimaksud pasal 1 ayat 1 huruf a Perjanjian dan Debitur dengan 
            ini mengaku benar-benar secara sah telah berhutang kepada Kreditur atas jumlah hutang 
            pokok tersebut demikian berikut bunga, denda dan biaya-biaya lain serta lain-lain jumlah 
            yang wajib dibayar oleh Debitur kepada Kreditur berdasarkan Perjanjian. 
        </li>
    </ol>
</div>

{{-- ═══════════════════════════ HALAMAN 3 ═══════════════════════════ --}}
<div class="page">

    <ol class="num" start="4">
        <li>
            Debitur menyetujui bahwa jumlah yang terhutang oleh Debitur kepada Kreditur
            berdasarkan Perjanjian ini pada waktu-waktu tertentu akan terbukti dari :
            <ol class="alpha">
                <li>Rekening Debitur yang dipegang dan dipelihara oleh Kreditur; dan/atau</li>
                <li>Buku-buku, catatan-catatan yang dipegang dan dipelihara oleh Kreditur; dan/atau</li>
                <li>Surat-surat dan dokumen-dokumen lain yang dikeluarkan oleh Kreditur; dan/atau</li>
                <li>Salinan/kutipan rekening Debitur.</li>
            </ol>
        </li>
    </ol>

    <p class="pasal-title">Pasal 4<br>Peristiwa Cidera Janji</p>

    <p>
        Dengan tetap memperhatikan ketentuan Pasal 2 ayat 1 Perjanjian ini, Kreditur berhak untuk
        sewaktu-waktu dengan mengesampingkan ketentuan pasal 1266 kitab Undang-Undang
        Hukum Perdata, khususnya ketentuan yang mengatur keharusan untuk mengajukan
        permohonan pembatalan perjanjian melalui pengadilan, sehingga tidak diperlukan suatu
        pemberitahuan (somasi) atau surat lain yang serupa dengan itu serta surat peringatan dari
        juru sita, menagih hutang Debitur berdasarkan Perjanjian ini atau sisanya, berikut
        bunga-bunga, denda-denda dan biaya lain yang timbul berdasarkan Perjanjian dan wajib
        dibayar oleh Debitur dengan seketika dan sekaligus lunas, apabila terjadi salah satu atau
        lebih kejadian-kejadian tersebut dibawah ini :
    </p>

    <ol class="num">
        <li>Debitur tidak atau lalai membayar lunas pada waktunya kepada Kreditur baik angsuran pokok, bunga-bunga, denda-denda dan biaya lainnya yang sudah jatuh tempo berdasarkan Perjanjian;</li>
        <li>Debitur meninggal dunia atau berada di bawah pengampuan;</li>
        <li>Debitur janda/ duda menikah kembali;</li>
        <li>Debitur dinyatakan pailit, diberikan penundaan membayar hutang-hutang (SURSEANCE VAN BETALING) atau bilamana Debitur dan/ atau orang/pihak lain mengajukan permohonan kepada instansi yang berwenang agar Debitur dinyatakan dalam keadaan pailit;</li>
        <li>Kekayaan Debitur baik sebagian maupun seluruhnya disita atau dinyatakan dalam sitaan oleh instansi yang berwenang;</li>
        <li>Debitur lalai atau tidak memenuhi syarat-syarat dan ketentuan/kewajiban dalam Perjanjian ini dan setiap perubahannya;</li>
        <li>Debitur lalai atau tidak memenuhi kewajibannya kepada pihak lain berdasarkan perjanjian dengan pihak lain sehingga Debitur dinyatakan cidera janji;</li>
        <li>Debitur tersangkut dalam suatu perkara hukum yang dapat menghalangi Debitur memenuhi kewajiban berdasarkan perjanjian ini sebagaimana mestinya;</li>
        <li>Apabila ternyata suatu pernyataan-pernyataan atau dokumen-dokumen atau keterangan-keterangan yang diberikan Debitur kepada Kreditur ternyata tidak benar atau tidak sesuai dengan kenyataan.</li>
        <li>Blokir angsuran dapat dicairkan berdasarkan ketentuan bank untuk menjaga kesehatan kredit bank dan debitur.</li>
    </ol>

    <p class="pasal-title">Pasal 5<br>Jaminan</p>

    <p>
        Untuk menjamin pembayaran hutang pokok, bunga dan pembayaran lainnya sebagaimana
        tercantum dalam Perjanjian ini, Debitur setuju dan sepakat memberikan jaminan kepada
        Kreditur berupa uang pensiun debitur setiap bulan, dan oleh karenanya Debitur dengan ini
        telah menyerahkan kepada Kreditur dokumen jaminan berupa :
    </p>

    <ol class="num">
        <li>Asli Surat Keputusan (SK) pensiunan Nomor : <span class="blank">{{ $no_sk_pensiun }}</span> Atas nama <span class="blank">{{ $nama_sk_pensiun }}</span></li>
        <li>Asli Surat Pernyataan Kuasa Potong Gaji dari Debitur tanggal <span class="blank">{{ $tgl_mulai }}</span> atas nama <span class="blank">{{ $nama_debitur }}</span></li>
        <li>Bukti Sertifikat Kepesertaan Asuransi Jiwa Kredit atas nama <span class="blank">{{ $nama_debitur }}</span></li>
    </ol>

    <p>
        Jaminan-jaminan tersebut di atas hanya akan dikembalikan kepada Debitur atau hanya dapat
        dimintakan pengembaliannya oleh Debitur, sepanjang seluruh kewajiban kredit/utang Debitur
        telah dilunasi dengan baik dan telah dinyatakan lunas oleh Kreditur.
    </p>
</div>

{{-- ═══════════════════════════ HALAMAN 4 ═══════════════════════════ --}}
<div class="page">
    <p class="pasal-title">Pasal 6<br>Pernyataan dan Jaminan</p>

    <p>Debitur dengan ini menyatakan dan menjamin Kreditur hal-hal sebagai berikut :</p>

    <ol class="num">
        <li>Debitur mempunyai wewenang untuk menandatangani dan mengikatkan diri ke dalam Perjanjian ini.</li>
        <li>Debitur menyatakan dan menjamin bahwa Perjanjian ini tidak bertentangan dengan perjanjian apapun yang dibuat oleh Debitur dengan pihak ketiga.</li>
        <li>Dalam hal terjadi debitur meninggal dunia, maka ahli waris wajib melaporkan secara tertulis atau lisan kepada kreditur paling lambat 7 (tujuh) hari setelah debitur meninggal dunia. Apabila ahli waris lalai dalam laporan tersebut, maka kredit akan menjadi tanggung jawab ahli waris dalam menyelesaikan kewajiban di pihak kreditur.</li>
        <li>Debitur dengan ini menyatakan dan menjamin bahwa pada waktu ini tidak ada sesuatu hal atau peristiwa yang merupakan suatu kejadian kelalaian/pelanggaran sebagaimana dimaksudkan dalam pasal 4 Perjanjian ini.</li>
        <li>Debitur dengan ini menyatakan dan menjamin akan mengganti segala kerugian yang diderita oleh Kreditur sehubungan dengan adanya tuntutan atau gugatan dari pihak ketiga yang diakibatkan oleh karena adanya keterangan/pernyataan yang tidak benar yang disampaikan Debitur kepada Kreditur.</li>
        <li>Debitur menyatakan dan menjamin bahwa apa yang dijaminkan dalam Perjanjian ini adalah benar merupakan hak dan kewenangan Debitur sendiri dan tidak sedang terikat sebagai jaminan dan tidak akan dialihkan haknya pada pihak lain sampai dengan seluruh hutang Debitur dinyatakan lunas oleh Kreditur.</li>
        <li>Apabila debitur janda atau duda penerima manfaat pensiun menikah kembali, maka wajib melunasi seluruh fasilitas kredit.</li>
        <li>Debitur dengan ini menyatakan telah mengetahui dan memahami bahwa dana pencairan kredit yang diterima Debitur adalah berasal atau bersumber dari PT Bank KB Indonesia Tbk, yang diproses melalui Mitra Channeling dalam hal ini adalah Koperasi Nata Buana Pasundan.</li>
        <li>Debitur dengan ini menyatakan telah mengetahui, mengaku dan sepakat bahwa PT Bank KB Indonesia Tbk selaku Kreditur, berdasarkan pertimbangannya, dapat dan berwenang untuk menentukan menyimpan seluruh asli dan copy dari dokumen kredit dan dokumen jaminan untuk disimpan di kantor PT Bank KB Indonesia Tbk.</li>
        <li>Debitur dengan ini menyatakan dan menjamin untuk tidak memindahkan/mengalihkan kantor bayar uang pensiun debitur pada kantor bayar selain PT. Bank KB Indonesia, Tbk yang telah disepakati oleh Kreditur dan Debitur yang telah menerima surat kuasa pemotongan uang pensiun sampai dengan seluruh hutang Debitur dinyatakan lunas oleh Kreditur.</li>
        <li>Uang pencairan kredit yang di transfer ke rekening Debitur, sepenuhnya menjadi tanggung jawab Debitur. Jika Debitur gagal take over atau melakukan penyalahgunaan uang tersebut untuk diluar dari maksud dari Perjanjian ini, maka Debitur bersedia dituntut atau diproses secara hukum.</li>
        <li>Apabila terjadi kejadian gagal debet yang menimbulkan kondisi kredit tidak lancar, maka debitur harus bersedia dilakukan pemotongan pada gaji bulan berikutnya.</li>
    </ol>
</div>

{{-- ═══════════════════════════ HALAMAN 5 ═══════════════════════════ --}}
<div class="page">

    <p class="pasal-title">Pasal 7<br>Pemberian Kuasa</p>

    <ol class="num">
        <li>Debitur dengan ini memberikan kuasa kepada Kreditur untuk mendebet dan menggunakan dana yang tersimpan pada Kreditur baik dari rekening tabungan/deposito milik Debitur guna pembayaran angsuran pokok maupun bunga, denda, premi asuransi, biaya-biaya lainnya yang mungkin timbul sehubungan dengan pemberian fasilitas kredit ini dan segala yang terhutang berkenaan dengan pemberian fasilitas kredit berdasarkan Perjanjian ini.</li>
        <li>Berdasarkan pertimbangan dan kebijakan sendiri, untuk menjaga hak dan kepentingan Bank selaku Kreditur, maka Debitur dengan ini sepakat dan mengakui hak dan kewenangan Bank untuk melakukan setiap dan segala upaya hukum yang diperkenankan berdasarkan Perjanjian ini dan/atau peraturan perundang-undangan yang berlaku, termasuk tetapi tidak terbatas pada penjualan portofolio (asset sales) atau pengalihan tagihan Bank atas utang Debitur melalui cessie kepada pihak lain manapun, cukup dengan pemberitahuan tertulis kepada Debitur, tanpa atau dengan persetujuan Debitur.</li>
        <li>Kreditur diberi kuasa oleh Debitur untuk menutup asuransi jiwa dan biaya premi menjadi beban Debitur, apabila Debitur meninggal dunia, maka uang klaim asuransi dapat digunakan menjamin pelunasan seluruh kewajiban Debitur, kecuali karena satu dan lain hal, berdasarkan keputusan perusahaan asuransi, perusahaan asuransi menolak atau tidak dapat menyetujui klaim yang diajukan atau jika nilai klaim yang disetujui perusahaan asuransi ternyata kurang, lebih kecil atau tidak mencukupi seluruh kewajiban/utang Debitur. Dalam hal terjadi kondisi demikian, maka Debitur tetap wajib bertanggung jawab atas kewajiban/utangnya dimaksud.</li>
        <li>Kuasa-kuasa yang diberikan Debitur kepada Kreditur berdasarkan Perjanjian ini kata demi kata harus telah dianggap telah termaktub dalam Perjanjian ini dan merupakan satu kesatuan serta bagian yang tidak terpisahkan dengan Perjanjian ini yang tidak dibuat tanpa adanya kuasa tersebut, dan oleh karenanya kuasa-kuasa tersebut tidak akan dicabut dan tidak akan berakhir oleh karena sebab apapun juga, termasuk oleh sebab-sebab berakhirnya kuasa sebagaimana dimaksud dalam pasal 1813, 1814 dan 1816 kitab Undang-Undang Hukum Perdata. Namun demikian, apabila ternyata terdapat suatu ketentuan hukum yang mengharuskan adanya suatu kuasa khusus untuk melaksanakan hak Kreditur berdasarkan Perjanjian, maka Debitur atas permintaan pertama dari Kreditur wajib untuk memberikan kuasa khusus dimaksud kepada Kreditur.</li>
    </ol>

    <p class="pasal-title">Pasal 8<br>Lain-Lain</p>

    <ol class="num">
        <li>Debitur menyetujui dan dengan ini memberi kuasa kepada Kreditur untuk sewaktu-waktu menjual, mengalihkan, menjaminkan atau dengan cara apapun memindahkan piutang/tagihan-tagihan Kreditur kepada Debitur berdasarkan Perjanjian ini kepada pihak ketiga lainnya dengan siapa Kreditur membuat perjanjian kerja sama berikut semua hak, kekuasaan-kekuasaan dan jaminan-jaminan yang ada pada Kreditur berdasarkan Perjanjian ini atau Perjanjian jaminan, dengan syarat-syarat dan ketentuan-ketentuan yang dianggap baik oleh Kreditur.</li>
        <li>Debitur tidak diperkenankan untuk mengalihkan hak-hak dan kewajibannya bedasarkan Perjanjian ini kepada pihak manapun tanpa persetujuan tertulis terlebih dahulu dari Kreditur.</li>
        <li>Selama fasilitas kredit belum lunas, Debitur tidak diperkenankan untuk menerima pinjaman dari bank/pihak ketiga lainnya tanpa persetujuan dari Kreditur.</li>
        <li>Selama fasilitas kredit belum lunas, Debitur tidak diperkenankan untuk menunda pengambilan gajinya setiap bulan untuk memenuhi pembayaran angsuran kepada kreditur dan mengalihkan lokasi pembayaran uang pensiun Debitur ketempat lain selain yang telah disepakati oleh Kreditur dan Debitur yang telah menerima surat pernyataan, persetujuan dan kuasa pendebetan rekening.</li>
        <li>Dalam hal terjadi kegagalan dalam proses klaim dan pembayaran atas klaim asuransi jiwa atau dalam hal nilai klaim asuransi jiwa yang dapat disetujui dan dibayarkan oleh perusahaan asuransi tidak cukup untuk melunasi kewajiban/utang Debitur, maka sisa kewajiban/utang Debitur, baik pokok, bunga dan/atau denda, akan tetap menjadi tanggung jawab Debitur, dalam hal ini mengikat ahli waris Debitur.</li>
        <li>Debitur wajib mengijinkan Kreditur untuk melakukan pemeriksaan atas kekayaan dan/ usaha Debitur serta dan memeriksa pembukuan, catatan-catatan dan administrasi Debitur dan membuat salinan-salinan atau foto copy atau catatan-catatan dari padanya.</li>
        <li>Seluruh lampiran-lampiran Perjanjian ini termasuk namun tidak terbatas pada Perjanjian kerjasama, surat pernyataan, persetujuan dan kuasa pendebetan rekening, merupakan suatu kesatuan dan bagian yang tidak terpisahkan dengan Perjanjian.</li>
        <li>Hal-hal yang belum diatur dalam Perjanjian ini serta perubahan dan/atau penambahan akan ditentukan kemudian antara para pihak serta dituangkan secara tertulis dalam suatu Addendum yang ditandatangani bersama oleh para pihak serta merupakan bagian dan satu kesatuan yang tidak dapat dipisahkan dan mempunyai kekuatan hukum yang sama dengan Perjanjian ini, kecuali untuk hal-hal yang telah disetujui oleh Debitur yang dituangkan di dalam Perjanjian ini, persetujuan mana dianggap telah diberikan dengan ditandatanganinya Perjanjian Kredit.</li>
    </ol>
</div>

{{-- ═══════════════════════════ HALAMAN 6 ═══════════════════════════ --}}
<div class="page">

    <p class="pasal-title">Pasal 9<br>Hukum Yang Berlaku Dan Domisili Hukum</p>

    <ol class="num">
        <li>Perjanjian ini tunduk pada dan karenanya harus ditafsirkan berdasarkan hukum Republik Indonesia.</li>
        <li>Perjanjian ini telah disesuaikan dengan ketentuan peraturan perundang-undangan termasuk ketentuan peraturan Otoritas Jasa Keuangan.</li>
        <li>Untuk pelaksanaan Perjanjian ini dan segala akibatnya para pihak memilih tempat tinggal yang tetap dan tidak berubah di kantor Panitera Pengadilan Negeri domisili tergugat, dengan tidak mengurangi hak Kreditur untuk memohon pelaksanaan/eksekusi dari Perjanjian ini atau mengajukan tuntutan hukum terhadap Debitur melalui Pengadilan-Pengadilan Negeri lainnya dalam wilayah Republik Indonesia.</li>
    </ol>

    <p style="margin-top: 16px;">
        Demikian Perjanjian ini dibuat dan ditandatangani oleh para pihak pada hari ini dan tanggal
        sebagaimana disebutkan diawal Perjanjian ini dalam keadaan sadar dan tanpa ada paksaan
        dari pihak manapun.
    </p>

    <table class="ttd-table" style="margin-top: 40px;">
        <tr>
            <td style="text-align:center; width:50%;">
                <strong>Debitur</strong>
            </td>
            <td style="text-align:center; width:50%;">
                <strong>Kreditur</strong><br>
                <strong>PT Bank KB Indonesia Tbk</strong>
            </td>
        </tr>
        <tr>
            <td style="height:20px;"></td>
            <td style="text-align:center; font-size:10pt; color:#555;">Materai Rp. 10.000</td>
        </tr>
        <tr>
            <td style="height:70px;"></td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align:center;">
                (<span class="underline">{{ $nama_ttd_debitur }}</span>)
            </td>
            <td style="text-align:center;">
                (<span class="underline">{{ $nama_perwakilan_kb }}</span>)<br>
                Kuasa Kreditur
            </td>
        </tr>
    </table>
</div>


</body>
</html>
