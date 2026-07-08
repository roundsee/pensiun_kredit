<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banpot extends Model
{
    use HasFactory;

    protected $table = 'banpots';

    protected $fillable = [
        'import_batch_id',
        'source_filename',
        'sheet_name',
        'bulan',
        'tahun',
        'bank',
        'row_number',
        'no',
        'no_pk',
        'nopen',
        'norek',
        'total',
        'cabang',
        'status',
        'plafond',
        'selisih',
        'giro_mitra',
        'keterangan',
        'customer_id',
        'nama_debitur',
        'jenis_tagihan',
        'pengelola_pensiun',
        'pendebetan_angsuran',
        'nama_mitra_channeling',
        'row_data',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'row_number' => 'integer',
        'row_data' => 'array',
    ];
}