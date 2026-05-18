<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'external_employee_id',
        'name',
        'email',
        'status',
    ];

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class);
    }
}
