<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendidikan extends Model
{
    protected $table = 'pendidikans';

    public $timestamps = false;

    protected $fillable = [
        'pendidikan_name',
    ];

    public static function opsi(): array
    {
        return static::orderBy('pendidikan_name')->pluck('pendidikan_name', 'pendidikan_name')->all();
    }
}
