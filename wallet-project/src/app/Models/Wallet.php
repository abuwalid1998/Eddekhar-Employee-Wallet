<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $fillable = [
        'employee_id',
        'type',
        'currency',
        'available_balance',
        'reserved_balance',
        'status',
    ];

    protected $casts = [
        'available_balance' => 'integer',
        'reserved_balance' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
