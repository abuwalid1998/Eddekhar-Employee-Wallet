<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'type' => 'salary',
            'currency' => 'USD',
            'available_balance' => 1000,
            'reserved_balance' => 0,
            'status' => 'active',
        ];
    }
}
