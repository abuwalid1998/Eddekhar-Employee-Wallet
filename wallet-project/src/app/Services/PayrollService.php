<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollEvent;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

/**
 * @author Amjad Khaliliah
 */
class PayrollService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function processSalaryEvent(array $payload): array
    {
        return DB::transaction(function () use ($payload) {
            $existing = PayrollEvent::where(
                'external_event_id',
                $payload['external_event_id']
            )->first();

            if ($existing) {
                return ['event' => $existing, 'was_duplicate' => true];
            }

            $event = PayrollEvent::create([
                'external_event_id' => $payload['external_event_id'],
                'type' => $payload['type'],
                'payload' => $payload,
                'status' => 'processing',
            ]);

            $employee = Employee::where(
                'external_employee_id',
                $payload['employee_external_id']
            )->firstOrFail();

            $wallet = Wallet::where('employee_id', $employee->id)
                ->where('currency', strtoupper($payload['currency']))
                ->firstOrFail();

            $this->walletService->credit(
                wallet: $wallet,
                amount: $payload['amount'],
                type: 'payroll',
                description: 'Salary payment',
                metadata: [
                    'payroll_event_id' => $event->id,
                ]
            );

            $event->update([
                'status' => 'processed',
                'processed_at' => now(),
            ]);

            return ['event' => $event->fresh(), 'was_duplicate' => false];
        });
    }
}
