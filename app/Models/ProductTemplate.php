<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name',
        'template_description',
    ];

    public function templateAccounts()
    {
        return $this->hasMany(TemplateAccount::class);
    }

    public function templateFields()
    {
        return $this->hasMany(TemplateField::class)->orderBy('field_order');
    }
}
