<?php

namespace App\Services;

use App\Exceptions\Domain\CurrencyMismatchException;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class TransferService
{
    public function __construct(
        private WalletService $walletService
    ) {}

    public function transfer(
        Wallet $sourceWallet,
        Wallet $destinationWallet,
        int $amount,
        string $description = '',
        array $metadata = []
    ): array {
        if ($sourceWallet->currency !== $destinationWallet->currency) {
            throw new CurrencyMismatchException();
        }

        return DB::transaction(function () use (
            $sourceWallet,
            $destinationWallet,
            $amount,
            $description,
            $metadata
        ) {
            $debitTransaction = $this->walletService->debit(
                wallet: $sourceWallet,
                amount: $amount,
                type: 'transfer_out',
                description: $description ?: 'Transfer to another wallet',
                metadata: array_merge($metadata, [
                    'destination_wallet_id' => $destinationWallet->id,
                ])
            );

            $creditTransaction = $this->walletService->credit(
                wallet: $destinationWallet,
                amount: $amount,
                type: 'transfer_in',
                description: $description ?: 'Transfer from another wallet',
                metadata: array_merge($metadata, [
                    'source_wallet_id' => $sourceWallet->id,
                ])
            );

            return [
                'debit' => $debitTransaction,
                'credit' => $creditTransaction,
            ];
        });
    }
}
