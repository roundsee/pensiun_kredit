<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateField extends Model
{
    protected $fillable = [
        'product_template_id',
        'field_name',
        'field_label',
        'field_type',
        'is_required',
        'section',
        'account_code',
        'calculation_type',
        'default_value',
        'field_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'default_value' => 'decimal:4',
    ];

    public function productTemplate()
    {
        return $this->belongsTo(ProductTemplate::class);
    }
}
