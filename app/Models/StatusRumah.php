<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusRumah extends Model
{
    protected $table = 'status_rumahs';

    public $timestamps = false;

    protected $fillable = [
        'status_rumah_name',
    ];

    public static function opsi(): array
    {
        return static::orderBy('status_rumah_name')->pluck('status_rumah_name', 'status_rumah_name')->all();
    }
}
