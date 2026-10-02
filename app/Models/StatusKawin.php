<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusKawin extends Model
{
    protected $table = 'status_kawins';

    public $timestamps = false;

    protected $fillable = [
        'status_name',
    ];

    public static function opsi(): array
    {
        return static::orderBy('status_name')->pluck('status_name', 'status_name')->all();
    }
}
