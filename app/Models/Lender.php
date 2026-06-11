<?php

namespace App\Models;

use App\Models\JournalEntry;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lender extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'share_lender',
        'share_koperasi',
    ];

    protected $casts = [
        'share_lender' => 'decimal:2',
        'share_koperasi' => 'decimal:2',
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }
}
