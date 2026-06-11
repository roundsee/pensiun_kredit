<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KbReferenceOption extends Model
{
    protected $fillable = [
        'category',
        'value',
        'sort_order',
    ];
}
