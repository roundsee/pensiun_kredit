<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

class DataSimulasi extends Model
{
    use HasFactory;

    protected $table = 'data_simulasi';

    protected $fillable = [
        'status',
        'keterangan',
        'jenis_pensiun',
        'nama_debitur',
        'tanggal_lahir',
        'umur',
        'nomor_pensiun',
        'instansi',
        'gaji_pensiun',
        'sisa_gaji_saat_pengajuan',
        'produk',
        'rate_percent_override',
        'admin_angsuran_percent_override',
        'mutasi',
        'bank_asal',
        'bank_tujuan',
        'tenor_max',
        'plafond_max',
        'tenor',
        'nama_marketing',
        'kode_area',
        'usia_lunas',
        'tgl_permohonan',
        'tgl_lunas',
        'blokir_angsuran',
        'plafond',
        'angsuran',
        'biaya_adm_angs',
        'total_angsuran',
        'provisi',
        'administrasi',
        'asuransi',
        'extra_premi',
        'amount_blokir_angsuran',
        'pelunasan',
        'tata_laksana',
        'total_biaya',
        'sisa_gaji_akhir',
        'terima_bersih',
    ];

    protected $casts = [
        'status' => 'string',
        'tanggal_lahir' => 'date',
        'tgl_permohonan' => 'date',
        'tgl_lunas' => 'date',

        'umur' => 'integer',
        'tenor_max' => 'integer',
        'tenor' => 'integer',
        'usia_lunas' => 'integer',

        'gaji_pensiun' => 'float',
        'sisa_gaji_saat_pengajuan' => 'float',
        'plafond_max' => 'float',
        'blokir_angsuran' => 'float',
        'plafond' => 'float',
        'angsuran' => 'float',
        'biaya_adm_angs' => 'float',
        'total_angsuran' => 'float',
        'provisi' => 'float',
        'administrasi' => 'float',
        'asuransi' => 'float',
        'extra_premi' => 'float',
        'amount_blokir_angsuran' => 'float',
        'pelunasan' => 'float',
        'total_biaya' => 'float',
        'sisa_gaji_akhir' => 'float',
        'terima_bersih' => 'float',
    ];

    public function pelengkap(): HasOne
    {
        return $this->hasOne('App\\Models\\DataSimulasiPelengkap', 'data_simulasi_id');
    }

// protected static function booted(): void
// {
//     static::created(function (DataSimulasi $dataSimulasi) {
//         // Ambil nomor otomatis ke dalam variabel dulu untuk memastikan nilainya keluar
//         $noPk   = \App\Models\DataSimulasiPelengkap::generateNomorPK() ?? 'PK.0001/TEMP';
//         $noSppk = \App\Models\DataSimulasiPelengkap::generateNomorSPPK() ?? '0001/SPPK/TEMP';
//         $noSi   = \App\Models\DataSimulasiPelengkap::generateNomorSI() ?? 'SI-TO.0001/TEMP';
        
//         // PERBAIKAN: Kutipan (") dan kurung penutup ");" sudah ditambahkan dengan benar
//         Log::info("Generated nomor otomatis untuk DataSimulasi ID {$dataSimulasi->id}: no_pk={$noPk}, no_sppk={$noSppk}, no_si={$noSi}");

//         // Auto insert ke DataSimulasiPelengkap
//         \App\Models\DataSimulasiPelengkap::create([
//             'data_simulasi_id'        => $dataSimulasi->id, 
//             'no_pk'                   => $noPk, 
//             'no_sppk'                 => $noSppk,
//             'no_si'                   => $noSi,
//             'suku_bunga'              => 10.0,
//             'materai'                 => 80000.0,
//             'prosentase_provisi'      => 0.5,
//             'prosentase_administrasi' => 0.5,
//         ]);
//     });
// }
}