<?php

namespace Tests\Feature;

use App\Exceptions\Domain\InactiveWalletException;
use App\Exceptions\Domain\InsufficientFundsException;
use App\Models\Employee;
use App\Models\Wallet;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletServiceTest extends TestCase
{
    use RefreshDatabase;

    private WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->walletService = app(WalletService::class);
    }

    public function test_it_credits_wallet_balance(): void
    {
        $wallet = Wallet::factory()->create([
            'available_balance' => 10000,
        ]);

        $transaction = $this->walletService->credit(
            $wallet,
            5000,
            'deposit'
        );

        $wallet->refresh();

        $this->assertEquals(15000, $wallet->available_balance);
        $this->assertEquals('completed', $transaction->status);
    }

    public function test_it_debits_wallet_balance(): void
    {
        $wallet = Wallet::factory()->create([
            'available_balance' => 10000,
        ]);

        $this->walletService->debit(
            $wallet,
            3000,
            'withdrawal'
        );

        $wallet->refresh();

        $this->assertEquals(7000, $wallet->available_balance);
    }

    public function test_it_prevents_overdraft(): void
    {
        $wallet = Wallet::factory()->create([
            'available_balance' => 1000,
        ]);

        $this->expectException(InsufficientFundsException::class);

        $this->walletService->debit(
            $wallet,
            5000,
            'withdrawal'
        );
    }

    public function test_it_reserves_funds(): void
    {
        $wallet = Wallet::factory()->create([
            'available_balance' => 10000,
            'reserved_balance' => 0,
        ]);

        $this->walletService->reserveFunds($wallet, 4000);

        $wallet->refresh();

        $this->assertEquals(6000, $wallet->available_balance);
        $this->assertEquals(4000, $wallet->reserved_balance);
    }

    public function test_it_releases_reserved_funds(): void
    {
        $wallet = Wallet::factory()->create([
            'available_balance' => 6000,
            'reserved_balance' => 4000,
        ]);

        $this->walletService->releaseFunds($wallet, 4000);

        $wallet->refresh();

        $this->assertEquals(10000, $wallet->available_balance);
        $this->assertEquals(0, $wallet->reserved_balance);
    }

    public function test_it_blocks_inactive_wallets(): void
    {
        $wallet = Wallet::factory()->create([
            'status' => 'inactive',
        ]);

        $this->expectException(InactiveWalletException::class);

        $this->walletService->credit(
            $wallet,
            1000,
            'deposit'
        );
    }
}
