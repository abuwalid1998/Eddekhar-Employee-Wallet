<?php

namespace Database\Factories;

use App\Models\BankWithdrawal;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

class BankWithdrawalFactory extends Factory
{
    protected $model = BankWithdrawal::class;

    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'transaction_id' => Transaction::factory(),
            'amount' => 5000,
            'currency' => 'USD',
            'status' => 'pending',
            'bank_reference' => null,
        ];
    }
}
