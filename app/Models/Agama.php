<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agama extends Model
{
    protected $table = 'agamas';

    public $timestamps = false;

    protected $fillable = [
        'agama_name',
    ];

    public static function opsi(): array
    {
        return static::orderBy('agama_name')->pluck('agama_name', 'agama_name')->all();
    }
}
