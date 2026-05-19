<?php

namespace App\Services;

use App\Exceptions\Domain\InsufficientFundsException;
use App\Jobs\ProcessBankWithdrawal;
use App\Models\BankWithdrawal;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

/**
 * @author Amjad Khaliliah
 */
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
        $withdrawal = DB::transaction(function () use (
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

            return BankWithdrawal::create([
                'wallet_id' => $wallet->id,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'currency' => $wallet->currency,
                'status' => 'pending',
            ]);
        });

        ProcessBankWithdrawal::dispatch($withdrawal->id);

        return $withdrawal;
    }

    public function confirmWithdrawal(
        BankWithdrawal $withdrawal,
        string $bankReference
    ): void {
        DB::transaction(function () use ($withdrawal, $bankReference) {
            $withdrawal = BankWithdrawal::with(['wallet', 'transaction'])
                ->lockForUpdate()
                ->findOrFail($withdrawal->id);

            if ($withdrawal->status !== 'pending') {
                return;
            }

            $wallet = Wallet::lockForUpdate()->findOrFail($withdrawal->wallet_id);
            $transaction = $withdrawal->transaction;

            if ($wallet->reserved_balance < $withdrawal->amount) {
                throw new InsufficientFundsException('Reserved balance is insufficient.');
            }

            $wallet->reserved_balance -= $withdrawal->amount;
            $wallet->save();

            $transaction->status = 'completed';
            $transaction->save();

            $withdrawal->status = 'confirmed';
            $withdrawal->bank_reference = $bankReference;
            $withdrawal->save();
        });
    }

    public function failWithdrawal(BankWithdrawal $withdrawal): void
    {
        DB::transaction(function () use ($withdrawal) {
            $withdrawal = BankWithdrawal::with(['wallet', 'transaction'])
                ->lockForUpdate()
                ->findOrFail($withdrawal->id);

            if ($withdrawal->status !== 'pending') {
                return;
            }

            $wallet = Wallet::lockForUpdate()->findOrFail($withdrawal->wallet_id);
            $transaction = $withdrawal->transaction;

            if ($wallet->reserved_balance < $withdrawal->amount) {
                throw new InsufficientFundsException('Reserved balance is insufficient.');
            }

            $wallet->reserved_balance -= $withdrawal->amount;
            $wallet->available_balance += $withdrawal->amount;
            $wallet->save();

            $transaction->status = 'failed';
            $transaction->save();

            $withdrawal->status = 'failed';
            $withdrawal->save();
        });
    }
}
