<?php

namespace App\Services;

use App\Exceptions\Domain\CurrencyMismatchException;
use App\Exceptions\Domain\InsufficientFundsException;
use App\Exceptions\Domain\InactiveWalletException;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletService
{
    public function credit(
        Wallet $wallet,
        int $amount,
        string $type,
        string $description = '',
        array $metadata = []
    ): Transaction {
        return DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $description,
            $metadata
        ) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

            $this->assertWalletActive($wallet);

            $wallet->available_balance += $amount;
            $wallet->save();

            return $this->recordTransaction(
                wallet: $wallet,
                amount: $amount,
                type: $type,
                direction: 'credit',
                status: 'completed',
                description: $description,
                metadata: $metadata
            );
        });
    }

    public function debit(
        Wallet $wallet,
        int $amount,
        string $type,
        string $description = '',
        array $metadata = []
    ): Transaction {
        return DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $description,
            $metadata
        ) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

            $this->assertWalletActive($wallet);

            if ($wallet->available_balance < $amount) {
                throw new InsufficientFundsException();
            }

            $wallet->available_balance -= $amount;
            $wallet->save();

            return $this->recordTransaction(
                wallet: $wallet,
                amount: $amount,
                type: $type,
                direction: 'debit',
                status: 'completed',
                description: $description,
                metadata: $metadata
            );
        });
    }

    public function reserveFunds(Wallet $wallet, int $amount): void
    {
        DB::transaction(function () use ($wallet, $amount) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

            if ($wallet->available_balance < $amount) {
                throw new InsufficientFundsException();
            }

            $wallet->available_balance -= $amount;
            $wallet->reserved_balance += $amount;
            $wallet->save();
        });
    }

    public function releaseFunds(Wallet $wallet, int $amount): void
    {
        DB::transaction(function () use ($wallet, $amount) {
            $wallet = Wallet::lockForUpdate()->findOrFail($wallet->id);

            $wallet->reserved_balance -= $amount;
            $wallet->available_balance += $amount;
            $wallet->save();
        });
    }

    private function assertWalletActive(Wallet $wallet): void
    {
        if ($wallet->status !== 'active') {
            throw new InactiveWalletException();
        }
    }

    private function recordTransaction(
        Wallet $wallet,
        int $amount,
        string $type,
        string $direction,
        string $status,
        string $description,
        array $metadata
    ): Transaction {
        return Transaction::create([
            'wallet_id' => $wallet->id,
            'reference_id' => Str::uuid(),
            'type' => $type,
            'direction' => $direction,
            'amount' => $amount,
            'currency' => $wallet->currency,
            'status' => $status,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
