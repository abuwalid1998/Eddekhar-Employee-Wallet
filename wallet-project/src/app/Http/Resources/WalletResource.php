<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'type' => $this->type,
            'currency' => $this->currency,
            'available_balance' => $this->available_balance,
            'reserved_balance' => $this->reserved_balance,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
