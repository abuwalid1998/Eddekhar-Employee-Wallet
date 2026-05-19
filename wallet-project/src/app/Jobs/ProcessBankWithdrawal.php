<?php

namespace App\Jobs;

use App\Models\BankWithdrawal;
use App\Services\MockBankProviderService;
use App\Services\WithdrawalService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBankWithdrawal implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public int $withdrawalId
    ) {
    }

    public function handle(
        MockBankProviderService $bankProvider,
        WithdrawalService $withdrawalService
    ): void {
        $withdrawal = BankWithdrawal::with([
            'wallet',
            'transaction',
        ])->findOrFail($this->withdrawalId);

        if ($withdrawal->status !== 'pending') {
            return;
        }

        $result = $bankProvider->processWithdrawal(
            amount: $withdrawal->amount,
            currency: $withdrawal->currency
        );

        if ($result['success']) {
            $withdrawalService->confirmWithdrawal(
                $withdrawal,
                $result['bank_reference']
            );

            return;
        }

        $withdrawalService->failWithdrawal($withdrawal);
    }

    public function failed(\Throwable $exception): void
    {
        logger()->error('ProcessBankWithdrawal failed', [
            'withdrawal_id' => $this->withdrawalId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
