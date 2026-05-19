<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Wallet;
use App\Services\PayrollService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PayrollServiceTest extends TestCase
{
    use RefreshDatabase;

    private PayrollService $payrollService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payrollService = app(PayrollService::class);
    }

    public function test_it_processes_salary_event(): void
    {
        $employee = Employee::factory()->create();

        $wallet = Wallet::factory()->create([
            'employee_id' => $employee->id,
            'currency' => 'USD',
            'available_balance' => 0,
        ]);

        $payload = [
            'event_id' => (string) Str::uuid(),
            'type' => 'salary_paid',
            'employee_id' => $employee->id,
            'currency' => 'USD',
            'amount' => 50000,
        ];

        $this->payrollService->processSalaryEvent($payload);

        $wallet->refresh();

        $this->assertEquals(50000, $wallet->available_balance);
    }

    public function test_it_ignores_duplicate_events(): void
    {
        $employee = Employee::factory()->create();

        Wallet::factory()->create([
            'employee_id' => $employee->id,
            'currency' => 'USD',
            'available_balance' => 0,
        ]);

        $eventId = (string) Str::uuid();

        $payload = [
            'event_id' => $eventId,
            'type' => 'salary_paid',
            'employee_id' => $employee->id,
            'currency' => 'USD',
            'amount' => 50000,
        ];

        $this->payrollService->processSalaryEvent($payload);
        $this->payrollService->processSalaryEvent($payload);

        $this->assertDatabaseCount('payroll_events', 1);
    }
}
