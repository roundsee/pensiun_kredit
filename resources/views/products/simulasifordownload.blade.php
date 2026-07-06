<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Simulasi</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      background: #fff;
      margin: 0;
      padding: 0;
    }
.pdf-page {
            width: 60%;        /* same width as PDF */
            margin: auto;      /* center it */
            background: #fff;  /* white background like paper */
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2); /* paper shadow */
        }
.header {
  background: #28a745;
  color: white;
  padding: 8px;
  margin-bottom: 8px;

  display: flex;
  align-items: center; 
  justify-content: center;
  position: relative; /* needed for absolute logo */
}

.header-logo {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  background: white;
  padding: 2px;
  border-radius: 5px;
}

.header-logo img {
  height: 40px;
  display: block;
}
   
.header h2 {
      margin: 0;
      font-size: 16px;
    }
    .section-title {
      background: #0077b6;
      color: #fff;
      font-weight: bold;
      padding: 4px 6px;
      margin: 8px 0 4px 0;
      font-size: 14px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 6px;
    }
    td {
      padding: 3px 5px;
      vertical-align: top;
    }
    .label {
      width: 45%;
      font-weight: bold;
    }
    .highlight {
      background: #fffae6;
      font-weight: bold;
    }
    .num {
      text-align: right;
    }
  </style>
</head>
<body>
  <div class="pdf-page">
<table width="100%" style="background:#28a745; color:white; margin-bottom:8px;">
  <tr>
    <td style="width:60px; padding:8px; vertical-align:middle;">
     <img src="data:image/jpeg;base64,{{ $logo }}" height="60" alt="Logo">
    </td>

    <td style="text-align:center; padding:8px;">
      <div style="font-size:18px; font-weight:bold;">SIMULASI</div>
      <div style="font-size:18px; font-weight:bold;">NATA BUANA PASUNDAN</div>
      @if($sim->produk <> "Platinum")
      <div style="font-size:14px;">PENGAJUAN SISA GAJI</div>
      @endif
      @if($sim->produk == "Platinum")
      <div style="font-size:14px;">PENGAJUAN PLATINUM</div>
      @endif
      @if($sim->produk == "Regular")
      <div style="font-size:14px;">PENGAJUAN Regular</div>
      @endif
    </td>
  </tr>
</table>

</div>
    <table>
      <tr><td class="label">Tanggal Simulasi</td><td>{{ $sim->created_at->format('d-m-Y') }}</td></tr>
      @if($sim->produk == "SG")
        <tr><td class="label">Produk</td><td class="highlight">Sisa Gaji</td></tr>
      @endif
      @if($sim->produk == "Platinum")
        <tr><td class="label">Produk</td><td class="highlight">PLATINUM</td></tr>
      @endif
      @if($sim->produk == "Regular")
        <tr><td class="label">Produk</td><td class="highlight">Regular</td></tr>
      @endif

      <tr><td class="label">Nomor Pensiun</td><td class="highlight">{{ $sim->nomor_pensiun }}</td></tr>
      <tr><td class="label">Jenis Pensiun</td><td class="highlight">{{ $sim->jenis_pensiun }}</td></tr>
      <tr><td class="label">Bank Asal</td><td class="highlight">{{ $sim->bank_asal }}</td></tr>
      <tr><td class="label">Bank Tujuan</td><td class="highlight">{{ $sim->bank_tujuan }}</td></tr>
    </table>

    <div class="section-title">INPUT DATA</div>
    <table>
      <tr><td class="label">Nama Debitur</td><td class="highlight">{{ $sim->nama_debitur }}</td></tr>
      <tr><td class="label">Tanggal Lahir</td><td>{{ $sim->tanggal_lahir }}</td></tr>
      <tr><td class="label">Umur</td><td>{{ $sim->umur !== null ? $sim->umur . ' thn' : '-' }}</td></tr>
      <tr><td class="label">Instansi</td><td class="highlight">{{ $sim->instansi }}</td></tr>
      <tr><td class="label">Gaji Pensiun</td><td class="highlight">{{ number_format($sim->gaji_pensiun, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Angsuran Lainnya</td><td class="highlight">{{ number_format($sim->angsuran_lain, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Sisa Gaji saat Pengajuan</td><td>{{ number_format($sim->sisa_gaji_saat_pengajuan, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Tenor Max</td><td>{{ $sim->tenor_max }}</td></tr>
      <tr><td class="label">Plafond Max</td><td>{{ number_format($sim->plafond_max, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Tenor</td><td class="highlight">{{ $sim->tenor }}</td></tr>
      <tr><td class="label">Plafond</td><td class="highlight">{{ number_format($sim->plafond, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Blokir Angsuran (Bulan)</td><td class="highlight">{{ number_format($sim->blokir_angsuran, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Angsuran</td><td>{{ number_format($sim->angsuran, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Adm Angsuran</td><td>{{ number_format($sim->biaya_adm_angs, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Total Angsuran</td><td>{{ number_format($sim->total_angsuran, 0, ',', '.') }}</td></tr>
    </table>

    <div class="section-title">RINCIAN PEMBIAYAAN</div>
    <table>
      
      <tr><td class="label">Provisi</td><td>{{ number_format($sim->provisi, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Administrasi</td><td>{{ number_format($sim->administrasi, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Asuransi</td><td>{{ number_format($sim->asuransi, 0, ',', '.') }}</td></tr>
      @if($sim->product_kode == "SK-KB" || $sim->produk =="REGULAR")
        <tr><td class="label">Extra Premi</td><td>{{ number_format($sim->xtrapremi, 0, ',', '.') }}</td></tr>
      @endif
      <tr><td class="label">Blokir Angsuran</td><td>{{ number_format($sim->amount_blokir_angsuran, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Simpanan Pokok</td><td>{{ number_format($sim->simpanan_pokok, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Pelunasan</td><td class="highlight">{{ number_format($sim->pelunasan, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Tata Laksana</td><td class="highlight">{{ number_format($sim->tata_laksana, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Flagging</td><td class="highlight">{{ $sim->flagging }}</td></tr>
      <tr><td class="label">Nama Marketing</td><td class="highlight">{{ $sim->nama_marketing }}</td></tr>
      <tr><td class="label">Area</td><td class="highlight">{{ $sim->kode_area }}</td></tr>
      <tr><td class="label">Tgl Permohonan</td><td>{{ $sim->created_at }}</td></tr>
      <tr><td class="label">Tgl Lunas</td><td>{{ $sim->tgl_lunas }}</td></tr>
      <tr><td class="label">Usia Lunas</td><td>{{ $sim->usia_lunas }}</td></tr>
      <tr><td class="label">Total Biaya</td><td>{{ number_format($sim->total_biaya, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Sisa Gaji Akhir</td><td>{{ number_format($sim->sisa_gaji_akhir, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Terima Bersih</td><td>{{ number_format($sim->terima_bersih, 0, ',', '.') }}</td></tr>
    </table>
  </div>
</body>
</html>
