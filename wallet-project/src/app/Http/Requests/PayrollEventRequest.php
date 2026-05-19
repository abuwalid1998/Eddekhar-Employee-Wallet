<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'uuid'],
            'type' => ['required', 'string'],
            'employee_id' => ['required', 'exists:employees,id'],
            'currency' => ['required', 'string', 'size:3'],
            'amount' => ['required', 'integer', 'min:1'],
        ];
    }
}
