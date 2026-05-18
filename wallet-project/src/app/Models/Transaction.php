<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'reference_id',
        'type',
        'direction',
        'amount',
        'currency',
        'status',
        'description',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
