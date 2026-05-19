<?php

namespace Tests\Feature;

use App\Exceptions\Domain\CurrencyMismatchException;
use App\Models\Wallet;
use App\Services\TransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransferServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransferService $transferService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transferService = app(TransferService::class);
    }

    public function test_it_transfers_between_wallets(): void
    {
        $source = Wallet::factory()->create([
            'currency' => 'USD',
            'available_balance' => 10000,
        ]);

        $destination = Wallet::factory()->create([
            'currency' => 'USD',
            'available_balance' => 2000,
        ]);

        $this->transferService->transfer(
            $source,
            $destination,
            3000
        );

        $source->refresh();
        $destination->refresh();

        $this->assertEquals(7000, $source->available_balance);
        $this->assertEquals(5000, $destination->available_balance);
    }

    public function test_it_rejects_currency_mismatch(): void
    {
        $source = Wallet::factory()->create([
            'currency' => 'USD',
        ]);

        $destination = Wallet::factory()->create([
            'currency' => 'EUR',
        ]);

        $this->expectException(CurrencyMismatchException::class);

        $this->transferService->transfer(
            $source,
            $destination,
            1000
        );
    }
}
