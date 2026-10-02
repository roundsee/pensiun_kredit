<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pekerjaan extends Model
{
    protected $table = 'pekerjaans';

    public $timestamps = false;

    protected $fillable = [
        'pekerjaan_name',
    ];

    public static function opsi(): array
    {
        return static::orderBy('pekerjaan_name')->pluck('pekerjaan_name', 'pekerjaan_name')->all();
    }
}
