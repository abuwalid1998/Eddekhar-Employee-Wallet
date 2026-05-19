<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @author Amjad Khaliliah
 */
class PayrollEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'external_event_id' => [
                'required',
                'string',
                'max:255',
            ],
            'employee_external_id' => [
                'required',
                'string',
                'exists:employees,external_employee_id',
            ],
            'amount' => [
                'required',
                'integer',
                'min:1',
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
            ],
            'type' => [
                'required',
                'string',
                'in:salary,bonus',
            ],
        ];
    }
}
