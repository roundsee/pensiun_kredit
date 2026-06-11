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
      @if($sim->product_kode == "SG")
      <div style="font-size:14px;">PENGAJUAN SISA GAJI</div>
      @endif
      @if($sim->product_kode == "SK-KB")
      <div style="font-size:14px;">PENGAJUAN PLATINUM</div>
      @endif
      @if($sim->product_kode == "SK-REG")
      <div style="font-size:14px;">PENGAJUAN Regular</div>
      @endif
    </td>
  </tr>
</table>

</div>
    <table>
      <tr><td class="label">Tanggal Simulasi</td><td>{{ $sim->created_at->format('d-m-Y') }}</td></tr>
      @if($sim->product_kode == "SG")
        <tr><td class="label">Produk</td><td class="highlight">Sisa Gaji</td></tr>
      @endif
      @if($sim->product_kode == "SK-KB")
        <tr><td class="label">Produk</td><td class="highlight">PLATINUM</td></tr>
      @endif
      @if($sim->product_kode == "SK-REG")
        <tr><td class="label">Produk</td><td class="highlight">Regular</td></tr>
      @endif

      <tr><td class="label">Nomor Pensiun</td><td class="highlight">{{ $sim->notas }}</td></tr>
      <tr><td class="label">Jenis Pensiun</td><td class="highlight">{{ $sim->jenis_pensiun }}</td></tr>
      <tr><td class="label">Flagging</td><td class="highlight">{{$sim->flagstatus}}</td></tr>
      <tr><td class="label">Bank Asal</td><td class="highlight">{{ $sim->bank_asal }}</td></tr>
      <tr><td class="label">Bank Tujuan</td><td class="highlight">{{ $sim->bank_tujuan }}</td></tr>
    </table>

    <div class="section-title">INPUT DATA</div>
    <table>
      <tr><td class="label">Nama Debitur</td><td class="highlight">{{ $tu->nama_penerima }}</td></tr>
      <tr><td class="label">Tanggal Lahir</td><td>{{ $tu->tgl_lahir_penerima }}</td></tr>
      <tr><td class="label">Umur</td><td>{{ $sim->usia }}</td></tr>
      <tr><td class="label">Instansi</td><td class="highlight">{{ $sim->instansi }}</td></tr>
      <tr><td class="label">Gaji Pensiun</td><td class="highlight">{{ number_format($tu->bersih, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Angsuran Lainnya</td><td class="highlight">{{ number_format($sim->angsuran_lain, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Sisa Gaji saat Pengajuan</td><td>{{ number_format($sim->sisa_gaji, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Tenor Max</td><td>{{ $sim->max_tenor }}</td></tr>
      <tr><td class="label">Plafond Max</td><td>{{ number_format($sim->maxplafon, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Tenor</td><td class="highlight">{{ $sim->tenor }}</td></tr>
      <tr><td class="label">Plafond</td><td class="highlight">{{ number_format($sim->plafon, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Blokir Angsuran</td><td class="highlight">{{ number_format($sim->blokir, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Angsuran</td><td>{{ number_format($sim->angsuran, 0, ',', '.') }}</td></tr>
      @if($sim->product_kode != "SG")
      <tr><td class="label">Adm Angsuran</td><td>{{ number_format($sim->adm_angsuran, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Total Angsuran</td><td>{{ number_format($sim->adm_angsuran+$sim->angsuran, 0, ',', '.') }}</td></tr>
      @endif
    </table>

    <div class="section-title">RINCIAN PEMBIAYAAN</div>
    <table>
      
      <tr><td class="label">Provisi</td><td>{{ number_format($sim->provision, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Administrasi</td><td>{{ number_format($sim->administrasi, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Asuransi</td><td>{{ number_format($sim->asuransi, 0, ',', '.') }}</td></tr>
      @if($sim->product_kode == "SK-KB" || $sim->product_kode =="SK-REGULAR")
        <tr><td class="label">Extra Premi</td><td>{{ number_format($sim->xtrapremi, 0, ',', '.') }}</td></tr>
      @endif
      <tr><td class="label">Blokir Angsuran</td><td>{{ number_format($sim->blokir_amount, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Simpanan Pokok</td><td>{{ number_format($sim->simpananpokok, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Pelunasan</td><td class="highlight">{{ number_format($sim->pelunasan, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Tata Laksana</td><td class="highlight">{{ number_format($sim->tata_laksana, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Flagging</td><td class="highlight">{{ $sim->flagging }}</td></tr>
      <tr><td class="label">Nama Marketing</td><td class="highlight">{{ $user_name }}</td></tr>
      <tr><td class="label">Region</td><td class="highlight">{{ $sim->region }}</td></tr>
      <tr><td class="label">Area</td><td class="highlight">{{ $sim->area_name }}</td></tr>
      <tr><td class="label">Tgl Permohonan</td><td>{{ $sim->tgl_simulasi }}</td></tr>
      <tr><td class="label">Tgl Lunas</td><td>{{ $sim->tgl_lunas }}</td></tr>
      <tr><td class="label">Usia Lunas</td><td>{{ $sim->usia_lunas }}</td></tr>
      <tr><td class="label">Total Biaya</td><td>{{ number_format($sim->total_biaya, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Sisa Gaji Akhir</td><td>{{ number_format($sim->sisa_gaji_akhir, 0, ',', '.') }}</td></tr>
      <tr><td class="label">Terima Bersih</td><td>{{ number_format($sim->terima_bersih, 0, ',', '.') }}</td></tr>
    </table>
  </div>
</body>
</html>
