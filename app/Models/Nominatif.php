<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nominatif extends Model
{
    use HasFactory;

    protected $table = 'nominatifs';

    protected $fillable = [
        'import_batch_id',
        'source_filename',
        'sheet_name',
        'bulan',
        'tahun',
        'bank',
        'row_number',
        'no',
        'nopen',
        'norek',
        'tanggal',
        'nama',
        'mo',
        'plafond',
        'bunga',
        'angsuran_ke',
        'jw',
        'persen_byr',
        'baki_awal',
        'jt_pokok',
        'jt_bunga',
        'jt_swjb',
        'jt_jumlah',
        'blm_pokok',
        'blm_bunga',
        'blm_swjb',
        'blm_jumlah',
        'terima_pokok',
        'terima_bunga',
        'terima_swjb',
        'terima_jumlah',
        'baki_akhir',
        'kolektibilitas',
        'status_pembayaran',
        'row_data',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'row_number' => 'integer',
        'row_data' => 'array',
    ];
}