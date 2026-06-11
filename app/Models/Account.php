<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'account_group_id',
    ];

    public function group()
    {
        return $this->belongsTo(AccountGroup::class, 'account_group_id');
    }
}
