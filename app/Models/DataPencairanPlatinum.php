<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataPencairanPlatinum extends Model
{
    protected $table = 'data_pencairan_platinum';

    protected $fillable = [
        'no_urut',
        'tgl_akad',
        'nama',
        'nopen',
        'norek_kb',
        'no_tlp_hp',
        'no_pk',
        'plafond',
        'tenor',
        'angsuran',
        'pic',
        'korwil',
        'ao_kb',
        'kota',
        'verif',
        'tgl_cair_dp',
        'tgl_cair_pelunasan',
        'tgl_cair_sisa_bersih',
        'nominal_cair_dp',
        'nominal_cair_pelunasan',
        'nominal_cair_sisa_bersih',
        'pendana',
        'biaya_provisi',
        'biaya_admin',
        'biaya_asuransi',
        'materai_tl',
        'biaya_flagging',
        'total_potongan',
        'angsuran_dimuka',
        'angsuran_per_bulan_bank',
        'biaya_adm_angsuran',
        'total_angsuran',
        'fee_insentif_persen',
        'fee_insentif_nominal',
        'tat_flagging',
        'status_flagging',
        'tgl_kirim_email_flagging',
        'kendala_flagging',
        'keterangan_tambahan',
    ];

    protected $casts = [
        'no_urut' => 'integer',
        'tgl_akad' => 'date',
        'tgl_cair_dp' => 'date',
        'tgl_cair_pelunasan' => 'date',
        'tgl_cair_sisa_bersih' => 'date',
        'plafond' => 'decimal:2',
        'tenor' => 'integer',
        'angsuran' => 'decimal:2',
        'nominal_cair_dp' => 'decimal:2',
        'nominal_cair_pelunasan' => 'decimal:2',
        'nominal_cair_sisa_bersih' => 'decimal:2',
        'biaya_provisi' => 'decimal:2',
        'biaya_admin' => 'decimal:2',
        'biaya_asuransi' => 'decimal:2',
        'materai_tl' => 'decimal:2',
        'biaya_flagging' => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'angsuran_dimuka' => 'decimal:2',
        'angsuran_per_bulan_bank' => 'decimal:2',
        'biaya_adm_angsuran' => 'decimal:2',
        'total_angsuran' => 'decimal:2',
        'fee_insentif_persen' => 'decimal:2',
        'fee_insentif_nominal' => 'decimal:2',
    ];
}