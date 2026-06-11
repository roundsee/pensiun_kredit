<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimulationField extends Model
{
    use HasFactory;

    protected $fillable = [
        'simulation_batch_id',
        'source_filename',
        'product_id',
        'loan_id',
        'field_key',
        'field_label',
        'field_value',
        'section',
        'line_order',
        'raw_line',
        'extracted_at',
    ];

    protected $casts = [
        'extracted_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
