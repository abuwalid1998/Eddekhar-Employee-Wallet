<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankWithdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'transaction_id',
        'bank_reference',
        'amount',
        'currency',
        'status',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

}
