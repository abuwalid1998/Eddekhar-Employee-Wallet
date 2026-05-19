<?php

namespace App\Services;

use Illuminate\Support\Str;

class MockBankProviderService
{
    public function processWithdrawal(int $amount, string $currency): array
    {
        sleep(2);

        $success = random_int(1, 100) <= 85;

        if ($success) {
            return [
                'success' => true,
                'bank_reference' => 'BANK-' . Str::upper(Str::random(12)),
                'message' => 'Withdrawal processed successfully.',
            ];
        }

        return [
            'success' => false,
            'bank_reference' => null,
            'message' => 'Bank rejected withdrawal.',
        ];
    }
}
