<?php

namespace App\Models;

use App\Models\Account;
use App\Models\Lender;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_date',
        'account_id',
        'debit',
        'credit',
        'nasabah_id',
        'loan_id',
        'lender_id',
        'reference',
        'posting_status',
        'reversed_from_id',
        'void_reason',
        'description',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function lender()
    {
        return $this->belongsTo(Lender::class);
    }

    public function nasabah()
    {
        return $this->belongsTo(User::class, 'nasabah_id');
    }

    public function reversedFrom()
    {
        return $this->belongsTo(self::class, 'reversed_from_id');
    }

    public function reversals()
    {
        return $this->hasMany(self::class, 'reversed_from_id');
    }
}
