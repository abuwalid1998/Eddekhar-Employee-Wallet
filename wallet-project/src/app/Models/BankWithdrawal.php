<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankWithdrawal extends Model
{
    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'bank_reference',
        'amount',
        'currency',
        'status',
    ];
}
