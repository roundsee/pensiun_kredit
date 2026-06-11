<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_template_id',
        'coa_code',
        'name',
        'percentage',
        'formula_type',
        'min_value',
        'max_value',
        'account_group_id',
    ];

    public function group()
    {
        return $this->belongsTo(\App\Models\AccountGroup::class, 'account_group_id');
    }

    public function productTemplate()
    {
        return $this->belongsTo(ProductTemplate::class);
    }
}
