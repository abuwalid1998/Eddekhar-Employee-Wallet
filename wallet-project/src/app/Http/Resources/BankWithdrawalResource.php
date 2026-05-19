<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankWithdrawalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'wallet_id' => $this->wallet_id,
            'transaction_id' => $this->transaction_id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'bank_reference' => $this->bank_reference,
            'created_at' => $this->created_at,
        ];
    }
}
