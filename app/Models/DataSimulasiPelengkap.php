<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class DataSimulasiPelengkap extends Model
{
    use HasFactory;

    protected $table = 'data_simulasi_pelengkap';

    protected $fillable = [
        'data_simulasi_id',
        'tgl_sppk',
        'no_sppk',
        'npwp',
        'nama_ibu_kandung',
        'tanggal_dropping',
        'due_date_pertama',
        'jenis_kredit',
        'kantor_bayar',
        'kota',
        'alamat',
        'alamat_2',
        'suku_bunga',
        'jenis_fasilitas',
        'bentuk_fasilitas',
        'prosentase_provisi',
        'prosentase_administrasi',
        'asuransi',
        'materai',
        'tgl_surat_pernyataan_kuasa_potong_gaji',
        'no_pk',
        'tanggal_pk',
        'nama_perwakilan_kb',
        'jabatan',
        'no_surat_kuasa_substitusi',
        'tanggal_surat_kuasa_substitusi',
        'no_ktp',
        'no_skep',
        'tanggal_skep',
        'no_hp',
        'prosentase_admin_bank',
        'angsuran_dibayar_dimuka',
        'norek',
        'cabang_kb',
        'nama_ao',
        'kode_ao',
        'ket',
        'nama_pasangan',
        'ktp_pasangan',
        'tgl_lahir_pasangan',
        'cabang',
        'no_si',
        'no',
        'dibuat_di',
        'hari',
        'tanggal',
        'nama',
        'rt',
        'rw',
        'kel',
        'kec',
        'kota_kab',
        'kode_pos',
        'nama_petugas_nbp',
        'jabatan_petugas',
        'nomor_substitusi_pic',
        'tanggal_substitusi',
        'plafond',
        'jw',
        'biaya_provisi',
        'biaya_administrasi_kredit',
        'asuransi_jiwa_kredit',
        'biaya_flagging',
        'total_biaya',
        'total_penerimaan',
        'angsuran_pokok_bunga_perbulan',
        'biaya_administrasi_angsuran_perbulan_baa',
        'jangka_waktu',
        'tgl_awal_kredit',
        'tgl_akhir_kredit',
        'angsuran_bank_baa',
        'terbilang_angsuran',
        'kali_angsuran',
        'tgl_akhir_kredit_penarikan',
        'nominal_penarikan',
        'terbilang_penarikan',
        'atas_nama_sk',
        'tgl_kuasa_potong_gaji',
        'atas_nama_kuasa_potong_gaji',
        'nama_kepesertaan_ajk',
        'idpb_file',
        'permohonan_cif_file',
        'pelunasan_to_kb_file',
        'perjanjian_kredit_template_version',
        'agama',
        'pendidikan',
        'status_kawin',
        'status_rumah',
        
    ];

    protected $casts = [
        'tgl_sppk' => 'date',
        'tanggal_dropping' => 'date',
        'due_date_pertama' => 'date',
        'tgl_surat_pernyataan_kuasa_potong_gaji' => 'date',
        'tanggal_pk' => 'date',
        'tanggal_surat_kuasa_substitusi' => 'date',
        'tanggal_skep' => 'date',
        'tgl_lahir_pasangan' => 'date',
        'tanggal' => 'date',
        'tanggal_substitusi' => 'date',
        'tgl_awal_kredit' => 'date',
        'tgl_akhir_kredit' => 'date',
        'tgl_akhir_kredit_penarikan' => 'date',
        'tgl_kuasa_potong_gaji' => 'date',

        'jw' => 'integer',
        'jangka_waktu' => 'integer',
        'kali_angsuran' => 'integer',

        'suku_bunga' => 'float',
        'prosentase_provisi' => 'float',
        'prosentase_administrasi' => 'float',
        'asuransi' => 'float',
        'materai' => 'float',
        'prosentase_admin_bank' => 'float',
        'angsuran_dibayar_dimuka' => 'float',
        'plafond' => 'float',
        'biaya_provisi' => 'float',
        'biaya_administrasi_kredit' => 'float',
        'asuransi_jiwa_kredit' => 'float',
        'biaya_flagging' => 'float',
        'total_biaya' => 'float',
        'total_penerimaan' => 'float',
        'angsuran_pokok_bunga_perbulan' => 'float',
        'biaya_administrasi_angsuran_perbulan_baa' => 'float',
        'angsuran_bank_baa' => 'float',
        'nominal_penarikan' => 'float',
    ];

    public function dataSimulasi(): BelongsTo
    {
        return $this->belongsTo(DataSimulasi::class, 'data_simulasi_id');
    }
// protected static function booted(): void
// {
//     static::creating(function ($pelengkap) {
//         // 1. Generate nomor otomatis jika field belum diisi oleh input user/PDF
//         if (empty($pelengkap->no_pk)) {
//             $pelengkap->no_pk = static::generateNomorPK() ?? 'PK.0001/TEMP';
//         }
        
//         if (empty($pelengkap->no_sppk)) {
//             $pelengkap->no_sppk = static::generateNomorSPPK() ?? '0001/SPPK/TEMP';
//         }
        
//         if (empty($pelengkap->no_si)) {
//             $pelengkap->no_si = static::generateNomorSI() ?? 'SI-TO.0001/TEMP';
//         }

//         // 2. Set nilai default finansial jika belum terisi
//         if (empty($pelengkap->suku_bunga)) {
//             $pelengkap->suku_bunga = 10.0;
//         }
//         if (empty($pelengkap->materai)) {
//             $pelengkap->materai = 80000.0;
//         }
//         if (empty($pelengkap->prosentase_provisi)) {
//             $pelengkap->prosentase_provisi = 0.5;
//         }
//         if (empty($pelengkap->prosentase_administrasi)) {
//             $pelengkap->prosentase_administrasi = 0.5;
//         }
        
//         \Illuminate\Support\Facades\Log::info("Auto-fill creating event berhasil dijalankan untuk DataSimulasi ID: {$pelengkap->data_simulasi_id}");
//     });
// }

// public static function generateNomorSI(): string
//     {
//         $now = Carbon::now();
//         $tahun = $now->year;
//         $bulan = $now->month;

//         // 1. Konversi angka bulan ke Romawi
//         $romawi = static::konversiKeRomawi($bulan);

//         // 2. Ambil nomor urut terakhir di tahun berjalan
//         // Diasumsikan nomor urut reset setiap pergantian tahun
//         // $terakhir = static::whereYear('created_at', $tahun)
//         //     ->latest('id')
//         //     ->first();
// $terakhir = static::whereYear('created_at', $tahun)
//         ->whereNotNull('no_si')
//         ->where('no_si', '!=', '')
//         ->latest('id')
//         ->first();            

//         if ($terakhir) {
//             // Ambil string nomor lama, misal dari "SI-TO.0001/..." diambil "0001"
//             $nomorLama = explode('/', $terakhir->no_si)[0]; // Hasil: "SI-TO.0001"
//             $urutLama = (int) explode('.', $nomorLama)[1]; // Hasil: 1
//             $urutBaru = $urutLama + 1;
//         } else {
//             $urutBaru = 1;
//         }

//         // 3. Tambahkan leading zero agar menjadi 4 digit (0001, 0002, dst)
//         $noUrutStr = str_pad($urutBaru, 4, '0', STR_PAD_LEFT);

//         // 4. Gabungkan sesuai format permintaan Anda
//         return "SI-TO.{$noUrutStr}/NBP_CH.KB/{$romawi}/{$tahun}";
//     }
// public static function generateNomorPK(): string
//     {
//         $now = Carbon::now();
//         $tahun = $now->year;
//         $bulan = $now->month;

//         // 1. Konversi angka bulan ke Romawi
//         $romawi = static::konversiKeRomawi($bulan);

//         // 2. Ambil nomor urut terakhir di tahun berjalan
//         // Diasumsikan nomor urut reset setiap pergantian tahun
//         // $terakhir = static::whereYear('created_at', $tahun)
//         //     ->latest('id')
//         //     ->first();
// $terakhir = static::whereYear('created_at', $tahun)
//         ->whereNotNull('no_pk')
//         ->where('no_si', '!=', '')
//         ->latest('id')
//         ->first();

//         if ($terakhir) {
//             // Ambil string nomor lama, misal dari "SI-TO.0001/..." diambil "0001"
//             $nomorLama = explode('/', $terakhir->no_pk)[0]; // Hasil: "SI-TO.0001"
//             $urutLama = (int) explode('.', $nomorLama)[1]; // Hasil: 1
//             $urutBaru = $urutLama + 1;
//         } else {
//             $urutBaru = 1;
//         }

//         // 3. Tambahkan leading zero agar menjadi 4 digit (0001, 0002, dst)
//         $noUrutStr = str_pad($urutBaru, 4, '0', STR_PAD_LEFT);

//         // 4. Gabungkan sesuai format permintaan Anda
//         return "PK.{$noUrutStr}/NBP_CH.KB/{$romawi}/{$tahun}";
//     }

//  public static function generateNomorSPPK(): string
//     {
//         $now = Carbon::now();
//         $tahun = $now->year;
//         $bulan = $now->month;

//         // 1. Konversi angka bulan ke Romawi
//         $romawi = static::konversiKeRomawi($bulan);

//         // 2. Ambil nomor urut terakhir di tahun berjalan
//         // Diasumsikan nomor urut reset setiap pergantian tahun
//         // $terakhir = static::whereYear('created_at', $tahun)
//         //     ->latest('id')
//         //     ->first();
// $terakhir = static::whereYear('created_at', $tahun)
//         ->whereNotNull('no_sppk')
//         ->where('no_sppk', '!=', '')
//         ->latest('id')
//         ->first();
//         if ($terakhir) {
//             // Ambil string nomor lama, misal dari "SI-TO.0001/..." diambil "0001"
//             $nomorLama = explode('/', $terakhir->no_sppk)[0]; // Hasil: "SI-TO.0001"
//             $urutLama = (int) explode('.', $nomorLama)[1]; // Hasil: 1
//             $urutBaru = $urutLama + 1;
//         } else {
//             $urutBaru = 1;
//         }

//         // 3. Tambahkan leading zero agar menjadi 4 digit (0001, 0002, dst)
//         $noUrutStr = str_pad($urutBaru, 4, '0', STR_PAD_LEFT);

//         // 4. Gabungkan sesuai format permintaan Anda
//         return "{$noUrutStr}/SPPK/KNBP-KB/{$romawi}/{$tahun}";
//     }
   

//     /**
//      * Helper untuk mengubah angka bulan menjadi string Romawi
//      */
//     private static function konversiKeRomawi(int $bulan): string
//     {
//         $map = [
//             1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
//             7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
//         ];

//         return $map[$bulan] ?? 'I';
//     }    
 }