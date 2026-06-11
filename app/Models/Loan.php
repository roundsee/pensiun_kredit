<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_number',
        'product_id',
        'nasabah_id',
        'lender_id',
        'amount_plafond',
        'interest_rate',
        'provision_fee',
        'admin_fee',
        'status',
        'disbursed_at',
        'debtor_data',
        'submission_data',
        'financial_data',
    ];

    protected $casts = [
        'amount_plafond' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'provision_fee' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'disbursed_at' => 'date',
        'debtor_data' => 'array',
        'submission_data' => 'array',
        'financial_data' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function lender()
    {
        return $this->belongsTo(Lender::class);
    }

    public function nasabah()
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
