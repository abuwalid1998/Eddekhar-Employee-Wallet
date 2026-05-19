<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external_employee_id' => ['required', 'uuid', 'unique:employees,external_employee_id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:employees,email'],
            'status' => ['nullable', 'in:active,inactive,terminated'],
        ];
    }
}
