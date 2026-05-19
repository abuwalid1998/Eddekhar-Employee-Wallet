<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollEvent;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function processSalaryEvent(array $payload): PayrollEvent
    {
        return DB::transaction(function () use ($payload) {
            $existing = PayrollEvent::where(
                'external_event_id',
                $payload['event_id']
            )->first();

            if ($existing) {
                return $existing;
            }

            $event = PayrollEvent::create([
                'external_event_id' => $payload['event_id'],
                'type' => $payload['type'],
                'payload' => $payload,
                'status' => 'processing',
            ]);

            $employee = Employee::findOrFail($payload['employee_id']);

            $wallet = Wallet::where('employee_id', $employee->id)
                ->where('currency', $payload['currency'])
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

            return $event;
        });
    }
}
