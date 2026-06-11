<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFinancial extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'item_name',
        'account_id',
        'calculation_type',
        'default_value',
        'transaction_type',
        'is_deducted_at_disbursement',
        'is_included_in_simulation',
    ];

    protected $casts = [
        'default_value' => 'decimal:2',
        'is_deducted_at_disbursement' => 'boolean',
        'is_included_in_simulation' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
