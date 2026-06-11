<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InsuranceRate extends Model
{
    protected $table = 'insurance_rates';

    protected $fillable = [
        'product',
        'tenor',
        'premium_per_million',
    ];

    protected $casts = [
        'tenor' => 'integer',
        'premium_per_million' => 'float',
    ];
}
