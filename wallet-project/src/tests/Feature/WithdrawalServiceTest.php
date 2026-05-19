<?php

namespace Tests\Feature;

use App\Jobs\ProcessBankWithdrawal;
use App\Models\BankWithdrawal;
use App\Models\Employee;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\MockBankProviderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WithdrawalFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_withdrawal_dispatches_async_job(): void
    {
        Queue::fake();

        $employee = Employee::factory()->create();

        $wallet = Wallet::factory()->create([
            'employee_id' => $employee->id,
            'available_balance' => 10000,
            'reserved_balance' => 0,
            'status' => 'active',
            'currency' => 'USD',
        ]);

        $response = $this->postJson('/api/withdrawals', [
            'wallet_id' => $wallet->id,
            'amount' => 5000,
            'description' => 'ATM withdrawal',
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(ProcessBankWithdrawal::class);

        $wallet->refresh();

        $this->assertEquals(5000, $wallet->available_balance);
        $this->assertEquals(5000, $wallet->reserved_balance);

        $this->assertDatabaseHas('bank_withdrawals', [
            'wallet_id' => $wallet->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $wallet->id,
            'status' => 'pending',
            'type' => 'withdrawal',
        ]);
    }

    public function test_successful_withdrawal_completes_transaction(): void
    {
        $employee = Employee::factory()->create();

        $wallet = Wallet::factory()->create([
            'employee_id' => $employee->id,
            'available_balance' => 10000,
            'reserved_balance' => 0,
            'status' => 'active',
            'currency' => 'USD',
        ]);

        $withdrawal = BankWithdrawal::factory()->create([
            'wallet_id' => $wallet->id,
            'amount' => 5000,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $transaction = Transaction::factory()->create([
            'wallet_id' => $wallet->id,
            'status' => 'pending',
            'type' => 'withdrawal',
            'direction' => 'debit',
            'amount' => 5000,
            'currency' => 'USD',
        ]);

        $withdrawal->update([
            'transaction_id' => $transaction->id,
        ]);

        $mockProvider = $this->mock(MockBankProviderService::class);

        $mockProvider->shouldReceive('processWithdrawal')
            ->once()
            ->andReturn([
                'success' => true,
                'bank_reference' => 'BANK-TEST-123',
            ]);

        dispatch_sync(new ProcessBankWithdrawal($withdrawal->id));

        $wallet->refresh();
        $withdrawal->refresh();
        $transaction->refresh();

        $this->assertEquals(0, $wallet->reserved_balance);
        $this->assertEquals('confirmed', $withdrawal->status);
        $this->assertEquals('completed', $transaction->status);
        $this->assertEquals('BANK-TEST-123', $withdrawal->bank_reference);
    }

    public function test_failed_withdrawal_releases_reserved_funds(): void
    {
        $employee = Employee::factory()->create();

        $wallet = Wallet::factory()->create([
            'employee_id' => $employee->id,
            'available_balance' => 5000,
            'reserved_balance' => 5000,
            'status' => 'active',
            'currency' => 'USD',
        ]);

        $withdrawal = BankWithdrawal::factory()->create([
            'wallet_id' => $wallet->id,
            'amount' => 5000,
            'currency' => 'USD',
            'status' => 'pending',
        ]);

        $transaction = Transaction::factory()->create([
            'wallet_id' => $wallet->id,
            'status' => 'pending',
            'type' => 'withdrawal',
            'direction' => 'debit',
            'amount' => 5000,
            'currency' => 'USD',
        ]);

        $withdrawal->update([
            'transaction_id' => $transaction->id,
        ]);

        $mockProvider = $this->mock(MockBankProviderService::class);

        $mockProvider->shouldReceive('processWithdrawal')
            ->once()
            ->andReturn([
                'success' => false,
                'bank_reference' => null,
            ]);

        dispatch_sync(new ProcessBankWithdrawal($withdrawal->id));

        $wallet->refresh();
        $withdrawal->refresh();
        $transaction->refresh();

        $this->assertEquals(10000, $wallet->available_balance);
        $this->assertEquals(0, $wallet->reserved_balance);
        $this->assertEquals('failed', $withdrawal->status);
        $this->assertEquals('failed', $transaction->status);
    }

    public function test_insufficient_funds_prevents_withdrawal(): void
    {
        $employee = Employee::factory()->create();

        $wallet = Wallet::factory()->create([
            'employee_id' => $employee->id,
            'available_balance' => 1000,
            'reserved_balance' => 0,
            'status' => 'active',
            'currency' => 'USD',
        ]);

        $response = $this->postJson('/api/withdrawals', [
            'wallet_id' => $wallet->id,
            'amount' => 5000,
        ]);

        $response->assertStatus(422);
    }
}
