<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PicNbp extends Model
{
    protected $table = 'idpb';

    protected $fillable = [
        'nama_petugas',
        'jabatan',
        'nomor_substitusi',
        'tanggal_substitusi',
    ];
}
