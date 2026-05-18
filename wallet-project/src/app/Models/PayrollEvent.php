<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollEvent extends Model
{
    protected $fillable = [
        'external_event_id',
        'type',
        'payload',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
