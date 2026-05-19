<?php

namespace Tests\Feature;

use App\Models\Wallet;
use App\Services\WithdrawalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalServiceTest extends TestCase
{
    use RefreshDatabase;

    private WithdrawalService $withdrawalService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withdrawalService = app(WithdrawalService::class);
    }

    public function test_it_initiates_withdrawal(): void
    {
        $wallet = Wallet::factory()->create([
            'available_balance' => 10000,
        ]);

        $withdrawal = $this->withdrawalService->initiateWithdrawal(
            $wallet,
            3000
        );

        $wallet->refresh();

        $this->assertEquals(7000, $wallet->available_balance);
        $this->assertEquals(3000, $wallet->reserved_balance);
        $this->assertEquals('pending', $withdrawal->status);
    }

    public function test_it_confirms_withdrawal(): void
    {
        $wallet = Wallet::factory()->create([
            'available_balance' => 10000,
        ]);

        $withdrawal = $this->withdrawalService->initiateWithdrawal(
            $wallet,
            3000
        );

        $this->withdrawalService->confirmWithdrawal($withdrawal);

        $wallet->refresh();
        $withdrawal->refresh();

        $this->assertEquals(0, $wallet->reserved_balance);
        $this->assertEquals('confirmed', $withdrawal->status);
    }

    public function test_it_fails_withdrawal(): void
    {
        $wallet = Wallet::factory()->create([
            'available_balance' => 10000,
        ]);

        $withdrawal = $this->withdrawalService->initiateWithdrawal(
            $wallet,
            3000
        );

        $this->withdrawalService->failWithdrawal($withdrawal);

        $wallet->refresh();
        $withdrawal->refresh();

        $this->assertEquals(10000, $wallet->available_balance);
        $this->assertEquals(0, $wallet->reserved_balance);
        $this->assertEquals('failed', $withdrawal->status);
    }
}
