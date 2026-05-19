<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_wallet_id' => ['required', 'exists:wallets,id'],
            'destination_wallet_id' => ['required', 'exists:wallets,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ];
    }
}
