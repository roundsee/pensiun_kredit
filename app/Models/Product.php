<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    public function fields()
    {
        return $this->hasMany(ProductField::class);
    }

    public function financials()
    {
        return $this->hasMany(ProductFinancial::class);
    }
}
