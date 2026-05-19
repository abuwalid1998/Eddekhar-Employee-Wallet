<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wallet_id' => ['required', 'exists:wallets,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ];
    }
}
