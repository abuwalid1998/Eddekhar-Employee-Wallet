<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $employee = Employee::create([
            'external_employee_id' => (string) Str::uuid(),
            'name' => 'Amjad Khaliliah',
            'email' => 'Amjad@example.com',
            'status' => 'active',
        ]);

        Wallet::create([
            'employee_id' => $employee->id,
            'type' => 'salary',
            'currency' => 'USD',
            'available_balance' => 5000,
            'reserved_balance' => 0,
            'status' => 'active',
        ]);
    }
}
