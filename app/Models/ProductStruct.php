<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStruct extends Model
{
    protected $table = 'product_struct';

    protected $fillable = [
        'produk',
        'plafond_min',
        'plafond_max',
        'tenor_max',
        'rate_percent',
        'provisi_percent',
        'usia_masuk_min',
        'usia_max',
        'admin_percent',
        'blokir_angsuran',
        'taspen',
        'tata_laksana',
        'tata_laksana_plus_percent',
        'admin_angsuran_percent',
        'dbr_percent',
        'asabri',
        'usia_masuk_max',
        'sort_order',
    ];
}
