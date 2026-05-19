<?php

namespace App\Services;

use App\Models\BankWithdrawal;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WithdrawalService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function initiateWithdrawal(
        Wallet $wallet,
        int $amount,
        string $description = 'Employee withdrawal'
    ): BankWithdrawal {
        return DB::transaction(function () use (
            $wallet,
            $amount,
            $description
        ) {
            $this->walletService->reserveFunds($wallet, $amount);

            $transaction = $this->walletService->recordPendingTransaction(
                wallet: $wallet,
                amount: $amount,
                type: 'withdrawal',
                direction: 'debit',
                description: $description
            );

            $withdrawal = BankWithdrawal::create([
                'wallet_id' => $wallet->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'currency' => $wallet->currency,
                'status' => 'pending',
            ]);

            // later:
            // ProcessBankWithdrawal::dispatch($withdrawal);

            return $withdrawal;
        });
    }

    public function confirmWithdrawal(BankWithdrawal $withdrawal): void
    {
        DB::transaction(function () use ($withdrawal) {
            $wallet = $withdrawal->wallet;

            $wallet->reserved_balance -= $withdrawal->amount;
            $wallet->save();

            $withdrawal->update([
                'status' => 'confirmed',
            ]);

            $withdrawal->transaction->update([
                'status' => 'completed',
            ]);
        });
    }

    public function failWithdrawal(BankWithdrawal $withdrawal): void
    {
        DB::transaction(function () use ($withdrawal) {
            $this->walletService->releaseFunds(
                $withdrawal->wallet,
                $withdrawal->amount
            );

            $withdrawal->update([
                'status' => 'failed',
            ]);

            $withdrawal->transaction->update([
                'status' => 'failed',
            ]);
        });
    }
}
