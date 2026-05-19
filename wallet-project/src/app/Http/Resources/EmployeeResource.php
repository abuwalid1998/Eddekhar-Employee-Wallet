<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'external_employee_id' => $this->external_employee_id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'wallets' => WalletResource::collection($this->whenLoaded('wallets')),
            'created_at' => $this->created_at,
        ];
    }
}
