<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateWalletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'exists:employees,id',
            ],
            'type' => [
                'required',
                'string',
                'in:salary,bonus,savings',
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
            ],
            'available_balance' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}
